<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;

class ReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:report');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var Container $container */
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        $today = new \DateTime('now');
        $today->modify('- 1 day');
        $max = $today->format('Y-m-d 23:59:59');
        $min = $today->format('Y-m-d 00:00:00');
        $date = $today->format('d.m.Y');

        $countRegistered = $em->createQuery("
            SELECT COUNT(u.id)
            FROM VidalMainBundle:User u
            WHERE u.created > '$min' AND u.created < '$max'
        ")->getSingleScalarResult();

        $text = "На портале Vidal.Ru $date было зарегистрировано участников: $countRegistered";
        # $this->send('7binary@gmail.com', 'Артем', $text, $text, false);
        $this->send('si-bu@yandex.ru', 'si-bu@yandex.ru', $text, $text, false);
    }

    public function send($email, $to, $body, $subject, $local = false)
    {
        $mail = new PHPMailer();

        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->From = 'maillist@vidal.ru';
        $mail->FromName = 'Портал «Vidal.ru»';
        $mail->Subject = $subject;
        $mail->Host = '127.0.0.1';
        $mail->Body = $body;
        $mail->addAddress($email, $to);
        $mail->addCustomHeader('Precedence', 'bulk');

        if ($local) {
            $mail->Host = 'smtp.yandex.ru';
            $mail->From = 'binacy@yandex.ru';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->SMTPAuth = true;
            $mail->Username = 'binacy@yandex.ru';
            $mail->Password = 'oijoijoij';
        }
        else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            $mail->Port = 25;
        }

        $result = $mail->send();
        $mail = null;

        return $result;
    }
}
