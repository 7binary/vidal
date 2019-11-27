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
use Vidal\MainBundle\Entity\Delivery;
use Vidal\MainBundle\Entity\DeliveryLog;
use Vidal\MainBundle\Entity\Digest;

class DeliveryStatsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:delivery_stats')
            ->setDescription('Fills delivery stats file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:delivery_stats started');
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set('memory_limit', -1);

        /** @var Container $container */
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        # логи все
        $groupedLogs = $em->createQuery('
            SELECT l.uniqueid, COUNT(l.id) total,
              (SELECT MAX(a.created) FROM VidalMainBundle:DeliveryLog a WHERE l.uniqueid = a.uniqueid) AS created 
            FROM VidalMainBundle:DeliveryLog l
            GROUP BY l.uniqueid
            ORDER BY created DESC
        ')->getResult();

        $grouped = array();
        foreach ($groupedLogs as $g) {
            $key = $g['uniqueid'];
            $created = new \DateTime($g['created']);
            $g['created'] = $created->format('d.m.Y');
            $g['failed'] = 0;
            $g['opened'] = 0;
            $g['opened_unique'] = 0;
            $grouped[$key] = $g;
        }

        # логи ошибок
        $groupedFails = $em->createQuery('
            SELECT l.uniqueid, COUNT(l.id) failed
            FROM VidalMainBundle:DeliveryLog l
            WHERE l.failed = 1
            GROUP BY l.uniqueid
        ')->getResult();

        foreach ($groupedFails as $g) {
            $key = $g['uniqueid'];
            $grouped[$key]['failed'] = $g['failed'];
        }

        # открытий
        $groupedOpened = $em->createQuery('
            SELECT o.uniqueid, COUNT(o.id) opened, COUNT(DISTINCT o.user) opened_unique
            FROM VidalMainBundle:DigestOpened o
            GROUP BY o.uniqueid
        ')->getResult();

        foreach ($groupedOpened as $g) {
            $key = $g['uniqueid'];
            $grouped[$key]['opened'] = $g['opened'];
            $grouped[$key]['opened_unique'] = $g['opened_unique'];
        }

        # коэффициент и название рассылки
        /** @var Delivery[] $deliveries */
        $deliveries = $em->createQuery('
            SELECT d
            FROM VidalMainBundle:Delivery d
        ')->getResult();

        foreach ($deliveries as $delivery) {
            $key = $delivery->getName();
            $title = $delivery->getTitle();
            $grouped[$key]['coef'] = $delivery->getCoef();
            $grouped[$key]['coefSent'] = $delivery->getCoefSent();
            $grouped[$key]['title'] = $title;
        }

        $file = $container->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'delivery_stats.json';
        file_put_contents($file, json_encode($grouped));

        $output->writeln('+++ vidal:delivery_stats completed!');
    }
}
