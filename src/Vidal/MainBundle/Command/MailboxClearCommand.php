<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\Mailbox;
use Vidal\MainBundle\Entity\User;

class MailboxClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:mailbox_clear');
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

        foreach ($mailsIds as $mailId) {
            try {
                $inbox->deleteMail($mailId);
            }
            catch (\Exception $e) {
                continue;
            }
        }
    }
}
