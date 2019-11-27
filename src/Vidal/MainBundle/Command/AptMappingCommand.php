<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Vidal\MainBundle\Entity\ProtecPrice;
use Vidal\MainBundle\Entity\ProtecProduct;
use Vidal\MainBundle\Entity\ProtecRegion;
use Vidal\MainBundle\Service\FtpImplicitSsl;
use Vidal\MainBundle\Service\ImplicitFtp;

class AptMappingCommand extends ContainerAwareCommand
{
    /** @var ProtecRegion[] */
    protected $regions = array();
    /** @var ProtecProduct */
    protected $products = array();

    protected function configure()
    {
        $this->setName('vidal:apteka-mapping');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:apteka-mapping started');
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set('memory_limit', -1);

        $dir = $this->getContainer()->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'apteka' . DIRECTORY_SEPARATOR;
        @mkdir($dir);

        $port = $this->getContainer()->getParameter('apteka_port');
        $login = $this->getContainer()->getParameter('apteka_login');
        $password = $this->getContainer()->getParameter('apteka_password');

        $this->dl($output, '185.248.101.161', $port, $login, $password, $dir, '/home/twigavid/Input/');

        $output->writeln('+++ vidal:apteka-mapping completed!');
    }

    protected function dl(OutputInterface $output, $host, $port, $login, $password, $dirLocal, $dirRemote)
    {
        $connection = ssh2_connect($host, $port);

        if (!ssh2_auth_password($connection, $login, $password)) {
            throw new \Exception('Unable to connect.');
        }

        $this->loadMapping($dirLocal, $dirRemote, $connection, $output);
    }

    protected function loadMapping($dirLocal, $dirRemote, $connection, OutputInterface $output)
    {
        $output->writeln('... loading Mapping');
        /** @var Container $container */
        /** @var EntityManager $em */
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $fileName = 'VidalProtecMapping.xls';
        $fileLocal = $dirLocal . $fileName;
        @ssh2_scp_recv($connection, $dirRemote . $fileName, $fileLocal);

        $objReader = new \PHPExcel_Reader_Excel5();
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($fileLocal);

        $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
        $array_data = array();
        foreach ($rowIterator as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
            if (1 == $row->getRowIndex()) {
                continue; //skip first row
            }
            $rowIndex = $row->getRowIndex();
            $array_data[$rowIndex] = array('A' => '', 'B' => '');

            foreach ($cellIterator as $cell) {
                $array_data[$rowIndex][$cell->getColumn()] = $cell->getCalculatedValue();
            }
        }

        if (!empty($array_data)) {
            $updateQuery = $em->createQuery('
                    UPDATE VidalMainBundle:ProtecProduct p
                    SET p.ProductID = :ProductID
                    WHERE p = :protecId
                ');
            $em->createQuery("UPDATE VidalMainBundle:ProtecProduct p SET p.ProductID = NULL")->execute();

            foreach ($array_data as $key => $data) {
                $ProductID = intval($data['A']);
                $protecId = intval($data['B']);
                $updateQuery->setParameter('ProductID', $ProductID)
                    ->setParameter('protecId', $protecId)
                    ->execute();
            }
        }
    }
}
