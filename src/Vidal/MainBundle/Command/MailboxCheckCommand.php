<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\Mailbox;
use Vidal\MainBundle\Entity\User;

class MailboxCheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:mailbox_check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $inbox = new \PhpImap\Mailbox('{mail.vidal.ru:143/imap/notls}INBOX', 'maillist@vidal.ru', 'Te7R2XeX');

        $mailsIds = $inbox->searchMailbox('ALL');
        if (!$mailsIds) {
            die('Mailbox is empty');
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $messageIds = $em->getRepository("VidalMainBundle:Mailbox")->getMessageIds();

        $selectQuery = $em->createQuery("
			SELECT u
			FROM VidalMainBundle:User u
			WHERE u.username = :email
		");

        foreach ($mailsIds as $mailId) {
            try {
                $mail = $inbox->getMail($mailId);
                $messageId = $mail->messageId;

                if (in_array($messageId, $messageIds)) {
                    continue;
                }

                $messageSubject = preg_replace('/[\x00-\x1F\x7F]/u', '', $mail->subject);
                $messageHtml = preg_replace('/[\x00-\x1F\x7F]/u', '', $mail->textHtml);
                $status = $this->findStatusCodeByRecipient($messageHtml);

                $mailbox = new Mailbox();
                $mailbox->setMessageId($messageId);
                $mailbox->setStatusCode($status);
                $mailbox->setToString($mail->toString);

                # если письмо не может быть доставлено по этому адресу (ошибки вида 5.1.1)
                if (!empty($status) && preg_match('/^5./', $status)) {
                    $output->writeln(' => ' . $messageId);
                    $output->writeln(' ... status: ' . $status);
                    $mailbox->setFailed(true);

                    # находим все встречающиеся e-mail в теле письма
                    $matches = array();
                    $emails = array();
                    preg_match_all('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}/i', $messageHtml, $matches);
                    $matches = empty($matches[0]) ? array() : array_unique($matches[0]);

                    foreach ($matches as $email) {
                        if ($email != 'maillist@vidal.ru' && $email != 'm.vlasenko@vidal.ru') {
                            /** @var User $user */
                            if ($user = $selectQuery->setParameter('email', $email)->getOneOrNullResult()) {
                                $emails[] = $email;
                                $output->writeln("... mail delivery fails for user: {$email}");
                                $user->addMailDeleteCounter();
                                $em->flush($user);
                                $em->getRepository('VidalMainBundle:User')->setDeliveryLogFailed($user);

                                $mailbox->setUserId($user->getId());
                                $mailbox->setCounter($user->getMailDeleteCounter());
                            }
                        }
                    }

                    $mailbox->setEmail(implode('; ', $emails));
                }

                $em->persist($mailbox);
                $em->flush($mailbox);

                # тут возможны ошибки базы данных, игнорируем их
                try {
                    $mailbox->setBody($messageHtml);
                    $mailbox->setSubject($messageSubject);
                    $em->flush($mailbox);
                }
                catch (\Exception $e) {
                }
            }
            catch (\Exception $e) {
                if (!empty($mailbox)) {
                    $mailbox->setError($e->getMessage());
                }
                continue;
            }
        }
    }

    /**
     * Find an status code in body content.
     *
     * @param string $body : the body
     * @param boolean $verbose : output messages
     *
     * @return string
     */
    private function findStatusCodeByRecipient($body, $verbose = false)
    {
        $arBody = explode("\r\n", $body);
        foreach ($arBody as $bodyLine) {
            $bodyLine = trim($bodyLine);
            // From string
            $statusCode = self::getStatusCodeFromPattern($bodyLine);
            if (!empty($statusCode)) {
                if ($verbose) {
                    echo 'Status code <strong>' . $statusCode . '</strong> found via code resolver pattern' . PHP_EOL;
                }
                return $statusCode;
            }
            // RFC 1893 (http://www.ietf.org/rfc/rfc1893.txt) return code
            if (preg_match('#\W([245]\.[01234567]\.[012345678])\W#', $bodyLine, $matches)) {
                if (stripos($bodyLine, 'Message-ID') !== false) {
                    break;
                }
                $statusCode = $matches[1];
                $statusCode = self::formatStatusCode($statusCode);
                if ($verbose) {
                    echo 'Status code <strong>' . $statusCode . '</strong> found via RFC 1893.' . PHP_EOL;
                }
                return $statusCode;
            }
            // RFC 821 (http://www.ietf.org/rfc/rfc821.txt) return code
            if (preg_match('#\]?: ([45][01257][012345]) #', $bodyLine, $matches) || preg_match('#^([45][01257][012345]) (?:.*?)(?:denied|inactive|deactivated|rejected|disabled|unknown|no such|not (?:our|activated|a valid))+#i', $bodyLine, $matches)) {
                $statusCode = $matches[1];
                // map to new RFC
                if ($statusCode == '450' || $statusCode == '550' || $statusCode == '551' || $statusCode == '554') {
                    $statusCode = '511';
                }
                elseif ($statusCode == '452' || $statusCode == '552') {
                    $statusCode = '422';
                }
                elseif ($statusCode == '421') {
                    $statusCode = '432';
                }
                $statusCode = self::formatStatusCode($statusCode);
                if ($verbose) {
                    echo 'Status code <strong>' . $statusCode . '</strong> found and converted via RFC 821.' . PHP_EOL;
                }
                return $statusCode;
            }
        }
        return null;
    }

    /**
     * Format status code from regexp.
     *
     * @param string
     *
     * @return string
     */
    private static function formatStatusCode($statusCode)
    {
        if (empty($statusCode)) {
            return null;
        }
        if (preg_match('#(\d\d\d)\s#', $statusCode, $match)) {
            $statusCode = $match[1];
        }
        elseif (preg_match('#(\d\.\d\.\d)\s#', $statusCode, $match)) {
            $statusCode = $match[1];
        }
        if (preg_match('#([245]\.[01234567]\.[012345678])(.*)#', $statusCode, $match)) {
            return $match[1];
        }
        elseif (preg_match('#([245][01234567][012345678])(.*)#', $statusCode, $match)) {
            preg_match_all('#.#', $match[1], $arStatusCode);
            if (is_array($arStatusCode[0]) && count($arStatusCode[0]) == 3) {
                return implode('.', $arStatusCode[0]);
            }
        }
        return null;
    }

    /**
     * Find status code from string.
     *
     * @param string
     *
     * @return string
     */
    private static function getStatusCodeFromPattern($pattern)
    {
        $statusCodeResolver = array(
            // regexp
            '[45]\d\d[- ]\#?([45]\.\d\.\d)' => 'x',
            'Diagnostic[- ][Cc]ode: smtp; ?\d\d\ ([45]\.\d\.\d)' => 'x',
            'Status: ([45]\.\d\.\d)' => 'x',
            // 4.2.0
            'not yet been delivered' => '4.2.0',
            'message will be retried for' => '4.2.0',
            // 4.2.2
            'benutzer hat zuviele mails auf dem server' => '4.2.2',
            'exceeded storage allocation' => '4.2.2',
            'mailbox full' => '4.2.2',
            'mailbox is full' => '4.2.2',
            'mailbox quota usage exceeded' => '4.2.2',
            'mailbox size limit exceeded' => '4.2.2',
            'mailfolder is full' => '4.2.2',
            'not enough storage space' => '4.2.2',
            'over ?quota' => '4.2.2',
            'quota exceeded' => '4.2.2',
            'quota violation' => '4.2.2',
            'user has exhausted allowed storage space' => '4.2.2',
            'user has too many messages on the server' => '4.2.2',
            'user mailbox exceeds allowed size' => '4.2.2',
            'user has Exceeded' => '4.2.2',
            // 4.3.2
            'delivery attempts will continue to be made for' => '4.3.2',
            'delivery temporarily suspended' => '4.3.2',
            'greylisted for 5 minutes' => '4.3.2',
            'greylisting in action' => '4.3.2',
            'server busy' => '4.3.2',
            'server too busy' => '4.3.2',
            'system load is too high' => '4.3.2',
            'temporarily deferred' => '4.3.2',
            'temporarily unavailable' => '4.3.2',
            'throttling' => '4.3.2',
            'too busy to accept mail' => '4.3.2',
            'too many connections' => '4.3.2',
            'too many sessions' => '4.3.2',
            'too much load' => '4.3.2',
            'try again later' => '4.3.2',
            'try later' => '4.3.2',
            // 4.4.7
            'retry timeout exceeded' => '4.4.7',
            'queue too long' => '4.4.7',
            // 5.1.1
            '554 delivery error:' => '5.1.1',
            'account has been disabled' => '5.1.1',
            'account is unavailable' => '5.1.1',
            'account not found' => '5.1.1',
            'address invalid' => '5.1.1',
            'address is unknown' => '5.1.1',
            'address unknown' => '5.1.1',
            'addressee unknown' => '5.1.1',
            'address_not_found' => '5.1.1',
            'bad address' => '5.1.1',
            'bad destination mailbox address' => '5.1.1',
            'destin. Sconosciuto' => '5.1.1',
            'destinatario errato' => '5.1.1',
            'destinatario sconosciuto o mailbox disatttivata' => '5.1.1',
            'does not exist' => '5.1.1',
            'email Address was not found' => '5.1.1',
            'excessive userid unknowns' => '5.1.1',
            'Indirizzo inesistente' => '5.1.1',
            'Invalid account' => '5.1.1',
            'invalid address' => '5.1.1',
            'invalid or unknown virtual user' => '5.1.1',
            'invalid mailbox' => '5.1.1',
            'invalid recipient' => '5.1.1',
            'mailbox not found' => '5.1.1',
            'mailbox unavailable' => '5.1.1',
            'nie istnieje' => '5.1.1',
            'nie ma takiego konta' => '5.1.1',
            'no mail box available for this user' => '5.1.1',
            'no mailbox here' => '5.1.1',
            'no one with that email address here' => '5.1.1',
            'no such address' => '5.1.1',
            'no such email address' => '5.1.1',
            'no such mail drop defined' => '5.1.1',
            'no such mailbox' => '5.1.1',
            'no such person at this address' => '5.1.1',
            'no such recipient' => '5.1.1',
            'no such user' => '5.1.1',
            'not a known user' => '5.1.1',
            'not a valid mailbox' => '5.1.1',
            'not a valid user' => '5.1.1',
            'not available' => '5.1.1',
            'not exists' => '5.1.1',
            'recipient address rejected' => '5.1.1',
            'recipient not allowed' => '5.1.1',
            'recipient not found' => '5.1.1',
            'recipient rejected' => '5.1.1',
            'recipient unknown' => '5.1.1',
            'server doesn\'t handle mail for that user' => '5.1.1',
            'this account is disabled' => '5.1.1',
            'this address no longer accepts mail' => '5.1.1',
            'this email address is not known to this system' => '5.1.1',
            'unknown account' => '5.1.1',
            'unknown address or alias' => '5.1.1',
            'unknown email address' => '5.1.1',
            'unknown local part' => '5.1.1',
            'unknown or illegal alias' => '5.1.1',
            'unknown or illegal user' => '5.1.1',
            'unknown recipient' => '5.1.1',
            'unknown user' => '5.1.1',
            'user disabled' => '5.1.1',
            'user doesn\'t exist in this server' => '5.1.1',
            'user invalid' => '5.1.1',
            'user is suspended' => '5.1.1',
            'user is unknown' => '5.1.1',
            'user not found' => '5.1.1',
            'user not known' => '5.1.1',
            'user unknown' => '5.1.1',
            'valid RCPT command must precede data' => '5.1.1',
            'was not found in ldap server' => '5.1.1',
            'we are sorry but the address is invalid' => '5.1.1',
            'unable to find alias user' => '5.1.1',
            'user doesn\'t have a yahoo\.[a-zA-Z]{2,3} account' => '5.1.1',
            // 5.1.2
            'domain isn\'t in my list of allowed rcpthosts' => '5.1.2',
            'esta casilla ha expirado por falta de uso' => '5.1.2',
            'host ?name is unknown' => '5.1.2',
            'no relaying allowed' => '5.1.2',
            'no such domain' => '5.1.2',
            'not our customer' => '5.1.2',
            'relay not permitted' => '5.1.2',
            'relay access denied' => '5.1.2',
            'relaying denied' => '5.1.2',
            'relaying not allowed' => '5.1.2',
            'this system is not configured to relay mail' => '5.1.2',
            'unable to relay' => '5.1.2',
            'unrouteable mail domain' => '5.1.2',
            'we do not relay' => '5.1.2',
            // 5.1.6
            'old address no longer valid' => '5.1.6',
            'recipient no longer on server' => '5.1.6',
            // 5.1.8
            'dender address rejected' => '5.1.8',
            // 5.2.0
            'delivery failed' => '5.2.0',
            'exceeded the rate limit' => '5.2.0',
            'local Policy Violation' => '5.2.0',
            'mailbox currently suspended' => '5.2.0',
            'mail can not be delivered' => '5.2.0',
            'mail couldn\'t be delivered' => '5.2.0',
            'the account or domain may not exist' => '5.2.0',
            // 5.2.1
            'account disabled' => '5.2.1',
            'account inactive' => '5.2.1',
            'inactive account' => '5.2.1',
            'adressat unbekannt oder mailbox deaktiviert' => '5.2.1',
            'destinataire inconnu ou boite aux lettres desactivee' => '5.2.1',
            'mail is not currently being accepted for this mailbox' => '5.2.1',
            'el usuario esta en estado: inactivo' => '5.2.1',
            'email account that you tried to reach is disabled' => '5.2.1',
            'inactive user' => '5.2.1',
            'user is inactive' => '5.2.1',
            'mailbox disabled for this recipient' => '5.2.1',
            'mailbox has been blocked due to inactivity' => '5.2.1',
            'mailbox is currently unavailable' => '5.2.1',
            'mailbox is disabled' => '5.2.1',
            'mailbox is inactive' => '5.2.1',
            'mailbox locked or suspended' => '5.2.1',
            'mailbox temporarily disabled' => '5.2.1',
            'podane konto jest zablokowane administracyjnie lub nieaktywne' => '5.2.1',
            'questo indirizzo e\' bloccato per inutilizzo' => '5.2.1',
            'recipient mailbox was disabled' => '5.2.1',
            'domain name not found' => '5.2.1',
            // 5.4.4
            'couldn\'t find any host named' => '5.4.4',
            'couldn\'t find any host by that name' => '5.4.4',
            'perm_failure: dns error' => '5.4.4',
            'temporary lookup failure' => '5.4.4',
            'unrouteable address' => '5.4.4',
            'can\'t connect to' => '5.4.4',
            // 5.4.6
            'too many hops' => '5.4.6',
            // 5.5.0
            'content reject' => '5.5.0',
            'requested action aborted' => '5.5.0',
            // 5.5.2
            'mime/reject' => '5.5.2',
            // 5.5.3
            'mail data refused' => '5.5.3',
            // 5.5.4
            'mime error' => '5.5.4',
            // 5.6.2
            'rejecting password protected file attachment' => '5.6.2',
            // 5.7.1
            '550 OU-00' => '5.7.1',
            '550 SC-00' => '5.7.1',
            '550 DY-00' => '5.7.1',
            '554 denied' => '5.7.1',
            'you have been blocked by the recipient' => '5.7.1',
            'requires that you verify' => '5.7.1',
            'access denied' => '5.7.1',
            'administrative prohibition - unable to validate recipient' => '5.7.1',
            'blacklisted' => '5.7.1',
            'blocke?d? for spam' => '5.7.1',
            'conection refused' => '5.7.1',
            'connection refused due to abuse' => '5.7.1',
            'dial-up or dynamic-ip denied' => '5.7.1',
            'domain has received too many bounces' => '5.7.1',
            'failed several antispam checks' => '5.7.1',
            'found in a dns blacklist' => '5.7.1',
            'ips blocked' => '5.7.1',
            'is blocked by' => '5.7.1',
            'mail Refused' => '5.7.1',
            'message does not pass domainkeys' => '5.7.1',
            'message looks like spam' => '5.7.1',
            'message refused by' => '5.7.1',
            'not allowed access from your location' => '5.7.1',
            'permanently deferred' => '5.7.1',
            'rejected by policy' => '5.7.1',
            'rejected by windows live hotmail for policy reasons' => '5.7.1',
            'rejected for policy reasons' => '5.7.1',
            'rejecting banned content' => '5.7.1',
            'sorry, looks like spam' => '5.7.1',
            'spam message discarded' => '5.7.1',
            'too many spams from your ip' => '5.7.1',
            'transaction failed' => '5.7.1',
            'transaction rejected' => '5.7.1',
            'wiadomosc zostala odrzucona przez system antyspamowy' => '5.7.1',
            'your message was declared spam' => '5.7.1',
        );
        foreach ($statusCodeResolver as $bounceBody => $bounceCode) {
            if (preg_match('#' . $bounceBody . '#is', $pattern, $matches)) {
                $statusCode = isset($matches[1]) ? $matches[1] : $bounceCode;
                $statusCode = self::formatStatusCode($statusCode);
                return $statusCode;
            }
        }
        return null;
    }
}
