<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Vidal\MainBundle\Geo\IPGeoBase;
use Vidal\MainBundle\Service\Mailer;

class TestController extends Controller
{
    /**
     * @Route("/phpinfo")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function phpInfoAction()
    {
        phpinfo();
        exit;
    }

    /**
     * @Route("/test/t")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function tAction()
    {
        return $this->render('VidalMainBundle:Test:t.html.twig');
    }

    /**
     * @Route("/test/geo")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function geoAction()
    {
        $ipReal = $this->container->get('request')->getClientIp();

        $ips = array($ipReal, '194.146.119.204', '213.5.135.38', '178.155.14.10', '37.204.216.115', '176.194.189.56', '95.213.194.94', '178.155.14.10');
        $geo = array();
        $gb = new IPGeoBase();

        foreach ($ips as $ip) {
            $data = $gb->getRecord($ip);

            $geo[] = array(
                'ip' => $ip,
                'city' => isset($data['city']) ? iconv('windows-1251', 'UTF-8', $data['city']) : null,
                'region' => isset($data['region']) ? iconv('windows-1251', 'UTF-8', $data['region']) : null,
                'district' => isset($data['district']) ? iconv('windows-1251', 'UTF-8', $data['district']) : null,
            );
        }

        return $this->render('VidalMainBundle:Test:geo.html.twig', array(
            'geo' => $geo,
        ));
    }

    /**
     * @Route("/test-gmail-yandex")
     */
    public function gmailYandexAction()
    {
        $this->checkEnv();
        $emailService = $this->container->get('email.service');

        $emailService->send(
            '7binary@gmail.com',
            array('html' => '<p>Hello, my friend!</p><p>Welcome to the website vidal.ru!</p>'),
            'Приглашение на портал Vidal.ru',
            false,
            array(
                'Precedence' => 'list',
                'List-Unsubscribe' => "https://www.vidal.ru/unsubscribe-digest/7/2014-02-20_15:22:18",
            ),
            'Hello, my friend! Welcome to the website vidal.ru!'
        );

        echo 'sent!';
        exit;
    }

    /**
     * @Route("/test-gmail-local")
     */
    public function gmailLocalAction()
    {
        $this->checkEnv();
        $emailService = $this->container->get('email.service');

        $emailService->send(
            '7binary@gmail.com',
            array('html' => '<p>Hello, my friend!</p><p>Welcome to the website vidal.ru!</p>'),
            'Приглашение на портал Vidal.ru',
            false,
            array(
                'Precedence' => 'list',
                'List-Unsubscribe' => "https://www.vidal.ru/unsubscribe-digest/7/2014-02-20_15:22:18",
            ),
            'Hello, my friend! Welcome to the website vidal.ru!',
            true
        );

        echo 'sent!';
        exit;
    }

    private function checkEnv()
    {
        if ($this->container->get('kernel')->getEnvironment() != 'dev') {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @Route("/test/mail")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function mailAction()
    {
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $cwsDebug = new \Cws\CwsDebug();
        $cwsDebug->setDebugVerbose();
        $cwsDebug->setEchoMode();
        $cwsMbh = new \Cws\MailBounceHandler\Handler($cwsDebug);
        // process mode
        $cwsMbh->setNeutralProcessMode(); // default
        //$cwsMbh->setMoveProcessMode();
        //$cwsMbh->setDeleteProcessMode();

        /*
         * Local mailbox
         */
        /*if ($cwsMbh->openImapLocal('/home/email/temp/mailbox') === false) {
            $error = $cwsMbh->getError();
            return;
        }*/
        /*
         * Remote mailbox
         */

        $cwsMbh->setMailboxHost('host.rusmedserv.com');
        $cwsMbh->setMailboxPort(143);
        $cwsMbh->setMailboxUsername('maillist@vidal.ru');
        $cwsMbh->setMailboxPassword('Te7R2XeX');

        $cwsMbh->setImapMailboxService(); // default
        //$cwsMbh->setMailboxHost('imap.mydomain.com'); // default 'localhost'
        //$cwsMbh->setMailboxPort(993); // default const MAILBOX_PORT_IMAP
        //$cwsMbh->setMailboxUsername('myusername');
        //$cwsMbh->setMailboxPassword('mypassword');
        //$cwsMbh->setMailboxSecurity(CwsMailBounceHandler::MAILBOX_SECURITY_SSL); // default const MAILBOX_SECURITY_NOTLS
        //$cwsMbh->setMailboxCertValidate(); // default const MAILBOX_CERT_NOVALIDATE
        //$cwsMbh->setMailboxName('SPAM'); // default 'INBOX'
        if ($cwsMbh->openImapRemote() === false) {
            $error = $cwsMbh->getError();
            return;
        }

        // process mails!
        $result = $cwsMbh->processMails();
        if (!$result instanceof \Cws\MailBounceHandler\Models\Result) {
            $error = $cwsMbh->getError();
        }
        else {
            // continue with Result
            $counter = $result->getCounter();
            echo '<h2>Counter</h2>';
            echo 'total : ' . $counter->getTotal() . '<br />';
            echo 'fetched : ' . $counter->getFetched() . '<br />';
            echo 'processed : ' . $counter->getProcessed() . '<br />';
            echo 'unprocessed : ' . $counter->getUnprocessed() . '<br />';
            echo 'deleted : ' . $counter->getDeleted() . '<br />';
            echo 'moved : ' . $counter->getMoved() . '<br />';
            $mails = $result->getMails();
            echo '<h2>Mails</h2>';
            foreach ($mails as $mail) {
                if (!$mail instanceof \Cws\MailBounceHandler\Models\Mail) {
                    continue;
                }
                echo '<h3>' . $mail->getToken() . '</h3>';
                echo 'subject : ' . $mail->getSubject() . '<br />';
                echo 'type : ' . $mail->getType() . '<br />';
                echo 'recipients :<br />';
                foreach ($mail->getRecipients() as $recipient) {
                    if (!$recipient instanceof \Cws\MailBounceHandler\Models\Recipient) {
                        continue;
                    }
                    echo '- ' . $recipient->getEmail() . '<br />';
                }
            }
        }

        exit;
    }
}
