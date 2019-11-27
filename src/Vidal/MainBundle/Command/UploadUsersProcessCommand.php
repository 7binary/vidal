<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\DeliveryLog;
use Vidal\MainBundle\Entity\UploadUsers;

class UploadUsersProcessCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:upload_users_process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $container = $this->getContainer();
        $testMode = $container->getParameter('env_local');
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        /** @var UploadUsers[] $models */
        $models = $em->getRepository('VidalMainBundle:UploadUsers')->findToProcess();

        foreach ($models as $model) {
            $output->writeln('--- vidal:upload_users_process. ' . $model->getDeliveryId());
            $model->setStatus(UploadUsers::STATUS_PROCESSING);
            $em->flush($model);
            $deliveryId = $model->getDeliveryId();

            # пользователи
            $users = $em->createQuery("
                SELECT u.username, u.id, DATE_FORMAT(u.created, '%Y-%m-%d_%H:%i:%s') as created,
                    u.firstName, u.lastName, u.surName, u.hash, u.password
                FROM VidalMainBundle:User u
                WHERE u.autoregister = TRUE 
                  AND u.emailConfirmed = FALSE
                  AND (u.mail_delete_counter IS NULL OR u.mail_delete_counter <= 5)
            ")->getResult();

            if ($testMode) {
                $users = $em->createQuery("
                    SELECT u.username, u.id, DATE_FORMAT(u.created, '%Y-%m-%d_%H:%i:%s') as created,
                        u.firstName, u.lastName, u.surName, u.hash, u.password
                    FROM VidalMainBundle:User u
                    WHERE u.username = 'binarya@yandex.ru'
                ")->getResult(); #  7binary@gmail.com  si-bu@yandex.ru
            }

            $emailService = $this->getContainer()->get('email.service');
            $templatingService = $this->getContainer()->get('templating');
            $total = count($users);
            $subject = 'VIDAL.ru – благодарим за регистрацию!';

            # рассылка
            for ($i = 0; $i < $total; $i++) {
                try {
                    $email = $users[$i]['username'];
                    # проверка, что не рассылали еще
                    $results = $em->createQuery("
                        SELECT COUNT(l.id) 
                        FROM VidalMainBundle:DeliveryLog l 
                        WHERE l.uniqueid = :deliveryId
                          AND l.email = :email
                    ")->setParameter('deliveryId', $deliveryId)
                        ->setParameter('email', $email)
                        ->getSingleScalarResult();

                    if ($results > 0 && !$testMode) {
                        continue;
                    }

                    # логирование и рассылка
                    $this->log($deliveryId, $users[$i], $em);

                    # отправка письма, временно исключаем @gmail
                    if (strpos($email, '@gmail.com') === false) {
                        $emailService->send(
                            $email,
                            array('VidalMainBundle:Email:autoregister_users.html.twig', array('user' => $users[$i], 'deliveryId' => $deliveryId)),
                            $subject,
                            $testMode,
                            array(
                                'Precedence' => 'bulk',
                                'List-Unsubscribe' => "https://www.vidal.ru/unsubscribe-digest/{$users[$i]['id']}/{$users[$i]['created']}",
                            ),
                            $templatingService->render('VidalMainBundle:Email:autoregister_users.txt.twig', array('user' => $users[$i], 'deliveryId' => $deliveryId))
                        );
                    }

                    $count = $i + 1;
                    $output->writeln("... $count / $total ($email)");
                    sleep(2);
                }
                catch (\Exception $e) {
                    if ($testMode) {
                        throw $e;
                    }
                    sleep(2);
                    continue;
                }
            }

            $model->setStatus(UploadUsers::STATUS_FINISHED);
            $em->flush($model);
            $output->writeln('+++ vidal:upload_users_process completed! ' . $model->getDeliveryId());
        }
    }

    private function log($deliveryId, $user, EntityManager $em)
    {
        # сохраняем лог, кому отправили
        $deliveryLog = new DeliveryLog;
        $deliveryLog->setEmail($user['username']);
        $deliveryLog->setUniqueid($deliveryId);
        $deliveryLog->setUserId($user['id']);
        $em->persist($deliveryLog);
        $em->flush($deliveryLog);
    }
}
