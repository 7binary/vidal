<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Vidal\MainBundle\Entity\ProtecPrice;
use Vidal\MainBundle\Entity\ProtecProduct;
use Vidal\MainBundle\Entity\ProtecRegion;
use Vidal\MainBundle\Service\FtpImplicitSsl;
use Vidal\MainBundle\Service\ImplicitFtp;

class AptUpdateCommand extends ContainerAwareCommand
{
    protected $products = array();
    protected $prices = array();
    protected $now;
    protected $from;
    protected $to;

    protected function configure()
    {
        $this->setName('vidal:apteka-update')
            ->addArgument('fromTo', InputArgument::REQUIRED, 'From-to looking files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:apteka-update started');
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set('memory_limit', -1);

        $fromTo = $input->getArgument('fromTo');
        list($from, $to) = explode('-', $fromTo);
        $this->from = $from;
        $this->to = $to;

        $now = new \DateTime();
        $this->now = $now->format('Y-m-d H:i:s');

        $dir = $this->getContainer()->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'apteka' . DIRECTORY_SEPARATOR;
        @mkdir($dir);

        $this->dl($output, '188.254.54.61', 8821, 'ftpuser1', 'Vidal#ftpuser2017!', $dir, '/Input/');

        $output->writeln('+++ vidal:apteka-update completed!');
    }

    protected function dl(OutputInterface $output, $host, $port, $login, $password, $dirLocal, $dirRemote, $debug = false)
    {
        $conn_id = ftp_connect($host, $port);
        $login_result = ftp_login($conn_id, $login, $password);
        ftp_pasv($conn_id, true);

        $data = array();

        for ($i = $this->from; $i <= $this->to; $i++) {
            $region_id = $i . '';
            $fileName = "ZDRAVCITY_$i.CSV";
            $fileLocal = $dirLocal . $fileName;
            $fileRemote = $dirRemote . $fileName;

            if (@ftp_get($conn_id, $fileLocal, $fileRemote, FTP_BINARY)) {
                if (file_exists($fileLocal)) {
                    $output->writeln('... ' . $fileName);
                    $data[$region_id] = array();
                    $csvFile = file($fileLocal);

                    foreach ($csvFile as $line) {
                        $line = mb_convert_encoding($line, "utf-8", "windows-1251");
                        $data[$region_id][] = str_getcsv($line, ';');
                    }
                }
            }
        }

        $this->loadData($data, $output, $debug);
        $this->loadMapping($dirLocal, $dirRemote, $conn_id, $output);
        ftp_close($conn_id);
    }

    protected function loadData($data, OutputInterface $output, $debug)
    {
        /** @var Container $container */
        /** @var EntityManager $em */
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $pdo = $em->getConnection();
        $pdo->prepare("SET FOREIGN_KEY_CHECKS=0")->execute();

        # находим все текущие цены в базе данных
        $stmt = $pdo->prepare("SELECT * FROM protec_price ORDER BY id");
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach ($results as $r) {
            $product_id = $r['product_id'];
            $key = $product_id . '+' . $r['region_id'];
            $this->prices[$key] = $r['price'] . '+' . $r['link'];
        }

        # находим все текущие препараты
        $stmt = $pdo->prepare("SELECT id, ProductID FROM protec_product ORDER BY id");
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $key = $p['id'] . '';
            $this->products[$key] = empty($p['ProductID']) ? 0 : $p['ProductID'];
        }

        # обрабатываем данные из загруженных файлов
        foreach ($data as $region_id => $products) {
            $output->writeln('... updating data from region_id ' . $region_id);

            foreach ($products as $p) {
                list($id, $title, $form, $stock, $priceStr, $link) = $p;
                $id .= '';

                if (!isset($this->products[$id])) {
                    $product = new ProtecProduct();
                    $product->setId($id);
                    $product->setForm($form);
                    $product->setTitle($title);
                    $em->persist($product);
                    $em->flush($product);
                    $this->products[$id] = $id;

                    if ($debug) {
                        $output->writeln('+ new product: ' . $id);
                    }
                }

                $key = $id . '+' . $region_id;
                $value = $priceStr . '+' . $link;

                try {
                    if (isset($this->prices[$key])) {
                        if ($this->prices[$key] != $value) {
                            $pdo->prepare("UPDATE protec_price SET price = '$priceStr', link = '$link', updated = '{$this->now}' WHERE region_id = $region_id AND product_id = $id")->execute();
                            if ($debug) {
                                $output->writeln("+ updated price: $key | {$this->prices[$key]} => {$value}");
                            }
                        }
                    }
                    else {
                        $pdo->prepare("INSERT INTO protec_price (region_id, product_id, price, link, updated, created) VALUES ($region_id, $id, '$priceStr', '$link', '{$this->now}', '{$this->now}')")->execute();
                        $this->prices[$key] = $value;
                        if ($debug) {
                            $output->writeln('+ new price: ' . $key);
                        }
                    }
                }
                catch (\Exception $e) {
                    continue;
                }
            }
        }
    }

    protected function loadMapping($dirLocal, $dirRemote, $conn_id, OutputInterface $output)
    {
        $output->writeln('... loading Mapping');
        /** @var Container $container */
        /** @var EntityManager $em */
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $fileName = 'VidalProtecMapping.xls';
        $fileLocal = $dirLocal . $fileName;
        $fileRemote = $dirRemote . $fileName;

        if (@ftp_get($conn_id, $fileLocal, $fileRemote, FTP_BINARY)) {
            if (file_exists($fileLocal)) {
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

                    foreach ($array_data as $key => $data) {
                        $ProductID = intval($data['A']);
                        $protecId = intval($data['B']);

                        if (!isset($this->products[$protecId]) || $this->products[$protecId] != $ProductID) {
                            $updateQuery->setParameter('ProductID', $ProductID)
                                ->setParameter('protecId', $protecId)
                                ->execute();
                        }
                    }
                }
            }
        }
    }
}
