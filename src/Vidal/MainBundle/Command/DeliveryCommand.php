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
use Vidal\MainBundle\Entity\DeliveryLog;
use Vidal\MainBundle\Entity\Digest;
use Vidal\MainBundle\Entity\Region;
use Vidal\MainBundle\Entity\Specialty;

class DeliveryCommand extends ContainerAwareCommand
{
    protected $problem_reported = false;

    protected function configure()
    {
        $this->setName('vidal:delivery')
            ->setDescription('Send digest')
            ->addOption('test', null, InputOption::VALUE_NONE, 'Send digest to manager e-mails')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop sending digests')
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Clean log app/logs/digest_sent.txt')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Send digest to every subscribed user')
            ->addOption('me', null, InputOption::VALUE_NONE, 'Send digest to 7binary@gmail.com')
            ->addOption('dev', null, InputOption::VALUE_NONE, 'Skip checking delivery file')
            ->addOption('local', null, InputOption::VALUE_NONE, 'Send digest from 7binary@list.ru');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set('memory_limit', -1);

        # если ни одна опция не указана - выводим мануал
        if (!$input->getOption('test') && !$input->getOption('clean') && !$input->getOption('all') && !$input->getOption('me') && !$input->getOption('local') && !$input->getOption('stop')) {
            $output->writeln('=> Error: uncorrect syntax. READ BELOW');
            $output->writeln('$ php app/console vidal:delivery --test');
            $output->writeln('$ php app/console vidal:delivery --stop');
            $output->writeln('$ php app/console vidal:delivery --clean');
            $output->writeln('$ php app/console vidal:delivery --all');
            $output->writeln('$ php app/console vidal:delivery --me');
            $output->writeln('$ php app/console vidal:delivery --me --local');

            return false;
        }

        /** @var Container $container */
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get();

        # --stop   остановка рассылки дайджеста
        if ($input->getOption('stop')) {
            $digest->setProgress(false);
            $em->flush();
            $this->fileRemove();
            $output->writeln('=> digest STOPPED');

            return true;
        }

        if ($input->getOption('clean')) {
            $em->createQuery('UPDATE VidalMainBundle:User u SET u.send=0 WHERE u.send=1')->execute();
            $digest->setProgress(false);
            $em->flush();
            $this->fileRemove();
            $output->writeln('=> users CLEANED');
            $output->writeln('=> digest STOPPED');
        }

        # рассылка нашим менеджерам
        if ($input->getOption('test')) {
            $raw = explode(';', $digest->getEmails());
            $emails = array();

            foreach ($raw as $email) {
                $emails[] = trim($email);
            }
            $output->writeln("=> Sending: in progress to managers: " . implode(', ', $emails));
            $this->sendTo($emails);
        }

        # отправить самому себе
        if ($input->getOption('me')) {
            $output->writeln("=> Sending: in progress to 7binary@bk.ru");
            $this->sendTo(array('7binary@bk.ru'), $input->getOption('local'));
        }

        # если статус рассылки не запущен или уже запущен с имеющимся файлом - прерываем
        if (!$digest->getProgress()) {
            $output->writeln('-- Digest progress is false @ database');
            return false;
        }

        if ($this->fileExists() && $input->getOption('dev') == false) {
            $output->writeln('-- Digest already has lock file created');
            return false;
        }

        # рассылка всем подписанным врачам
        if ($input->getOption('all')) {
            $output->writeln("=> Sending: in progress to ALL subscribed users...");
            $digest->setProgress(true);
            $em->flush();
            $this->fileCreate();
            $this->sendToAll($output);
        }

        return true;
    }

    private function fileExists()
    {
        return file_exists('/home/twigavid/public_html/.del');
        //return file_exists(__DIR__ . DIRECTORY_SEPARATOR . '.delivery');
    }

    private function fileRemove()
    {
        @unlink('/home/twigavid/public_html/.del');
        //@unlink(__DIR__ . DIRECTORY_SEPARATOR . '.delivery');
    }

    private function fileCreate()
    {
        $fp = @fopen('/home/twigavid/public_html/.del', "a+");
        //$fp = @fopen(__DIR__ . DIRECTORY_SEPARATOR . '.delivery', "a+");
        @fclose($fp);
    }

    private function sendToAll(OutputInterface $output)
    {
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $templating = $container->get('templating');

        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get();
        $subject = $digest->getSubject();
        $limit = $digest->getLimit();
        $totalLimit = $digest->getTotalLimit();
        $uniqueid = $digest->getUniqueid();

        $this->updateDelivery($em, $digest);

        /** @var Specialty[] $specialties */
        $specialties = $digest->getSpecialties();
        /** @var Region[] $regions */
        $regions = $digest->getRegions();

        $step = 30;
        $sleep = 10;

        # этим уже отправили
        $qb2 = $em->createQueryBuilder();
        $qb2->select('l.email')
            ->from('VidalMainBundle:DeliveryLog', 'l')
            ->where("l.uniqueid = '$uniqueid'");

        # пользователи
        $qb = $em->createQueryBuilder();
        $qb->select("u.username, u.id, DATE_FORMAT(u.created, '%Y-%m-%d_%H:%i:%s') as created, u.firstName")
            ->from('VidalMainBundle:User', 'u')
            ->andWhere($qb->expr()->notIn('u.username', $qb2->getDQL()))
            ->andWhere('u.digestSubscribed = 1')
            ->andWhere('(u.mail_delete_counter IS NULL OR u.mail_delete_counter <= 5)')
            ->orderBy('u.id', 'ASC');

        # специальности
        if (count($specialties)) {
            $specialtyIds = array();
            foreach ($specialties as $specialty) {
                $specialtyIds[] = $specialty->getId();
            }
            $qb->andWhere('u.primarySpecialty IN (:ids) OR u.secondarySpecialty IN (:ids)')
                ->setParameter('ids', $specialtyIds);
        }

        # регионы
        if (count($regions)) {
            $regionIds = array();
            foreach ($regions as $region) {
                $regionIds[] = $region->getId();
            }
            $qb->andWhere('u.region IN (:regionIds)')
                ->setParameter('regionIds', $regionIds);
        }

        $users = $qb->getQuery()->getResult();
        $template1 = $templating->render('VidalMainBundle:Digest:template1.html.twig', array('digest' => $digest));
        $checkLogQuery = $em->createQuery('SELECT l FROM VidalMainBundle:DeliveryLog l WHERE l.uniqueid = :uniqueid AND l.email = :email');

        # рассылка
        for ($i = 0; $i < count($users); $i++) {
            try {
                $email = $users[$i]['username'];
                $template2 = $templating->render('VidalMainBundle:Digest:template2.html.twig', array('user' => $users[$i], 'digest' => $digest));
                $template = $template1 . $template2;

                # проверяем лог, что по этой рассылке не отправляли
                if ($deliveryLog = $checkLogQuery->setParameter('uniqueid', $uniqueid)->setParameter('email', $email)->getOneOrNullResult()) {
                    continue;
                }

                # Яндексу побольше времени надо
                if (strpos($email, '@yandex.ru') !== false) {
                    sleep(7);
                }

                # сохраняем лог, кому отправили
                $deliveryLog = new DeliveryLog();
                $deliveryLog->setEmail($email);
                $deliveryLog->setUniqueid($uniqueid);
                $deliveryLog->setUserId($users[$i]['id']);
                $deliveryLog->setMessageId();
                $em->persist($deliveryLog);
                $em->flush($deliveryLog);

                # отправка письма, временно исключаем @gmail
                $listUnsub = "https://www.vidal.ru/unsubscribe-digest/{$users[$i]['id']}/{$users[$i]['created']}";
                $textPlain = $digest->getTextPlain();
                $send = $this->send($email, $users[$i]['firstName'], $template, $subject, false, $listUnsub, $textPlain, $deliveryLog->getMessageId());

                if ($send === 1) {
                    $deliveryLog->setSend($send);
                    $em->flush($deliveryLog);
                }
                else {
                    $errorsCounter = 0;
                    # ошибка при отправке, почтовый сервер не принял. Начинаем в цикле повторять отправку
                    if (false == filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    do {
                        $errorsCounter++;
                        if ($errorsCounter >= 15 && $this->problem_reported == false) {
                            $this->reportProblem($digest, $errorsCounter);
                        }
                        $deliveryLog->setError($send);
                        $deliveryLog->setMessageId();
                        $em->flush($deliveryLog);
                        sleep(60);
                        $send = $this->send($email, $users[$i]['firstName'], $template, $subject, false, $listUnsub, $textPlain, $deliveryLog->getMessageId());
                    }
                    while ($send !== 1);

                    $deliveryLog->setSend($send);
                    $em->flush($deliveryLog);
                }

                if ($i && $i % $step == 0) {
                    # проверка, можно ли продолжать рассылать
                    $em->refresh($digest);
                    if (false === $digest->getProgress()) {
                        break;
                    }

                    $subject = $digest->getSubject();
                    $limit = $digest->getLimit();
                    $totalLimit = $digest->getTotalLimit();

                    $em->getConnection()->close();
                    sleep($sleep);
                    $em->getConnection()->connect();
                }

                if ($totalLimit) {
                    # считаем, сколько отправлено
                    $totalSend = $em->createQuery('SELECT COUNT(DISTINCT l.email) FROM VidalMainBundle:DeliveryLog l WHERE l.uniqueid = :uniqueid')
                        ->setParameter('uniqueid', $digest->getUniqueid())
                        ->getSingleScalarResult();

                    if ($totalSend >= $totalLimit) {
                        # превышен максимальный лимит на отправку, прерываем цикл
                        $output->writeln('-- total limit is reached, break sending');
                        break;
                    }
                }

                if ($limit && $i && $i % $limit == 0) {
                    $em->getConnection()->close();
                    sleep(60 * 60);
                    $em->getConnection()->connect();
                }
                sleep(2);
            }
            catch (\Exception $e) {
                if (!empty($deliveryLog)) {
                    $deliveryLog->setError($e->getMessage());
                    $em->flush($deliveryLog);
                }

                continue;
            }
        }

        $digest->setProgress(false);

        $em->flush($digest);
        $this->fileRemove();

        $output->writeln('=> Completed!');
    }

    private function reportProblem(Digest $digest, $errorCounter)
    {
        $this->problem_reported = true;
        $emailService = $this->getContainer()->get('email.service');

        return true;

        return $emailService->send(
            'xxx',
            array('html' => "<p>Почтовый сервер VIDAL.RU отказывается принимать письма. Попыток: $errorCounter. Рассылка: {$digest->getUniqueid()}. Название: {$digest->getSubject()}</p>"),
            'Почтовый сервер VIDAL.RU загнулся',
            true,
            array(
                'Precedence' => 'list',
                'List-Unsubscribe' => "https://www.vidal.ru/unsubscribe-digest/7/2014-02-20_15:22:18",
            ),
            "Почтовый сервер VIDAL.RU отказывается принимать письма. Попыток: $errorCounter. Рассылка: {$digest->getUniqueid()}. Название: {$digest->getSubject()}"
        );
    }

    /**
     * Рассылка по массиву почтовых адресов без логирования
     *
     * @param array $emails
     */
    private function sendTo(array $emails, $local = false)
    {
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $templating = $container->get('templating');
        $emailService = $container->get('email.service');
        /** @var Digest $digest */
        $digest = $em->getRepository('VidalMainBundle:Digest')->get();

        $users = $em->createQuery("
			SELECT u.username, u.id, DATE_FORMAT(u.created, '%Y-%m-%d_%H:%i:%s') as created, u.firstName
			FROM VidalMainBundle:User u
			WHERE u.username IN (:emails)
		")->setParameter('emails', $emails)
            ->getResult();

        $subject = $digest->getSubject();
        $template1 = $templating->render('VidalMainBundle:Digest:template1.html.twig', array('digest' => $digest));

        foreach ($users as $user) {
            $template2 = $templating->render('VidalMainBundle:Digest:template2.html.twig', array('user' => $user, 'digest' => $digest));
            $html = $template1 . $template2;

            $emailService->send(
                $user['username'],
                array('html' => $html),
                $subject,
                $local,
                array(
                    'Precedence' => 'bulk',
                    'List-Unsubscribe' => "https://www.vidal.ru/unsubscribe-digest/{$user['id']}/{$user['created']}",
                ),
                $digest->getTextPlain()
            );
        }
    }

    /**
     * @param string $email
     * @param string $to
     * @param string $html
     * @param string $subject
     * @param bool $local
     * @param string | null $listUnsubscribe
     * @param string | null $textPlain
     * @return int
     * @throws \Exception
     */
    public function send($email, $to, $html, $subject, $local = false, $listUnsubscribe = null, $textPlain = null, $messageId = null)
    {
        try {
            $mail = new PHPMailer();

            $mail->isSMTP();
            $mail->isHTML(true);
            $mail->CharSet = "UTF-8";
            $mail->WordWrap = 80;
            $mail->From = 'maillist@vidal.ru';
            $mail->FromName = 'Портал «Vidal.ru»';
            $mail->Subject = $subject;
            $mail->Host = '127.0.0.1';
            $mail->Body = $html;
            $mail->addAddress($email, $to);
            $mail->addCustomHeader('Precedence', 'bulk');

            if (!empty($messageId)) {
                $mail->addCustomHeader('Message-ID', $messageId);
            }
            if (!empty($listUnsubscribe)) {
                $mail->addCustomHeader('List-Unsubscribe', $listUnsubscribe);
            }
            if (!empty($textPlain)) {
                $mail->AltBody = $textPlain;
            }

            if ($local) {
                $username = $this->getContainer()->getParameter('yandex_username');
                $password = $this->getContainer()->getParameter('yandex_password');

                $mail->Host = 'smtp.yandex.ru';
                $mail->From = $username;
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = $password;
            }
            else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
                $mail->Port = 26;
            }

            if (!$mail->send()) {
                throw new \Exception($mail->ErrorInfo);
            }
        }
        catch (\Exception $e) {
            return 'Mailer Error: ' . $e->getMessage();
        }

        # возвращаем либо текст ошибки отправки, либо 1
        return 1;
    }

    private function updateDelivery(EntityManager $em, Digest $digest)
    {
        $deliveryName = $digest->getUniqueid();
        $delivery = $em->getRepository("VidalMainBundle:Delivery")->getOrCreate($deliveryName);
        $delivery->setSubject($digest->getSubject());
        $delivery->setText($digest->getText());
        $delivery->setAllSpecialties($digest->getAllSpecialties());
        $delivery->setTextPlain($digest->getTextPlain());
        $delivery->setFooter($digest->getFooter());
        $delivery->setEmails($digest->getEmails());
        $delivery->setFont($digest->getFont());

        $specialties = array();
        foreach ($digest->getSpecialties() as $specialty) {
            /** @var Specialty $specialty */
            $specialties[] = $specialty->getTitle();
        }
        $specialties = empty($specialties) ? null : implode(', ', $specialties);

        $regions = array();
        foreach ($digest->getRegions() as $region) {
            /** @var Region $region */
            $regions[] = $region->getTitle();
        }
        $regions = empty($regions) ? null : implode(', ', $regions);

        $delivery->setSpecialties($specialties);
        $delivery->setRegions($regions);
        $em->flush($delivery);
    }
}
