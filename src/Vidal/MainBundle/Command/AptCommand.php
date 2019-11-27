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

class AptCommand extends ContainerAwareCommand
{
    /** @var ProtecRegion[] */
    protected $regions = array();
    /** @var ProtecProduct */
    protected $products = array();

    protected function configure()
    {
        $this->setName('vidal:apteka');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:apteka started');
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set('memory_limit', -1);

        $dir = $this->getContainer()->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'apteka' . DIRECTORY_SEPARATOR;
        @mkdir($dir);

        $this->importRegions();

        $port = $this->getContainer()->getParameter('apteka_port');
        $login = $this->getContainer()->getParameter('apteka_login');
        $password = $this->getContainer()->getParameter('apteka_password');

        $this->dl($output, '185.248.101.161', $port, $login, $password, $dir, '/home/twigavid/Input/');

        $output->writeln('+++ vidal:apteka completed!');
    }

    protected function dl(OutputInterface $output, $host, $port, $login, $password, $dirLocal, $dirRemote)
    {
        $connection = ssh2_connect($host, $port);

        if (!ssh2_auth_password($connection, $login, $password)) {
            throw new \Exception('Unable to connect.');
        }

        $data = array();

        for ($i = 1; $i <= 120; $i++) {
            $region_id = $i . '';
            $fileName = "ZDRAVCITY_$i.CSV";
            $fileLocal = $dirLocal . $fileName;
            @ssh2_scp_recv($connection, $dirRemote . $fileName, $fileLocal);

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

        $this->loadData($data, $output);
        $this->loadMapping($dirLocal, $dirRemote, $connection, $output);
    }

    protected function loadData($data, OutputInterface $output)
    {
        /** @var Container $container */
        /** @var EntityManager $em */
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $pdo = $em->getConnection();

        $query_insert_price = $pdo->prepare("INSERT INTO protec_price (region_id, product_id, price, link) VALUES (:region_id, :product_id, :price, :link)");

        foreach ($data as $region_id => $products) {
            $output->writeln('... loading data from region_id ' . $region_id);
            $region = isset($this->regions[$region_id]) ? $this->regions[$region_id] : null;

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
                }

                if ($region) {
                    $query_insert_price->bindParam(':region_id', $region_id);
                    $query_insert_price->bindParam(':product_id', $id);
                    $query_insert_price->bindParam(':link', $link);
                    $query_insert_price->bindParam(':price', $price);
                    $query_insert_price->execute();
                }
            }
        }
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

    protected function importRegions()
    {
        /** @var Container $container */
        /** @var EntityManager $em */
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $pdo = $em->getConnection();
        $pdo->prepare('SET FOREIGN_KEY_CHECKS  = 0')->execute();
        $pdo->prepare('DELETE FROM protec_region')->execute();
        $pdo->prepare('DELETE FROM protec_price')->execute();
        $pdo->prepare('DELETE FROM protec_product')->execute();

        foreach ($this->regionTitles as $id => $title) {
            /** @var ProtecRegion $region */
            if (($region = $em->getRepository("VidalMainBundle:ProtecRegion")->findOneById($id)) == null) {
                $region = new ProtecRegion();
                $region->setId($id);
                $region->setTitle($title);
                if ($title == 'Московская область') {
                    $region->setTitle2('Москва');
                }
                $em->persist($region);
                $em->flush($region);
                $em->refresh($region);
            }

            $id .= '';
            $this->regions[$id] = $region;
        }
    }

    protected $regionTitles = array(
        '16' => 'Республика Татарстан',
        '64' => 'Саратовская область',
        '30' => 'Астраханская область',
        '31' => 'Белгородская область',
        '46' => 'Курская область',
        '9' => 'Республика Карачаево-Черкессия',
        '23' => 'Краснодарский край',
        '35' => 'Вологодская область',
        '51' => 'Мурманская область',
        '39' => 'Калининградская область',
        '22' => 'Алтайский край',
        '63' => 'Самарская область',
        '7' => 'Кабардино-Балкарская Республика',
        '34' => 'Волгоградская область',
        '61' => 'Ростовская область',
        '26' => 'Ставропольский край',
        '27' => 'Хабаровский край',
        '42' => 'Кемеровская область',
        '19' => 'Республика Хакасия',
        '45' => 'Курганская область',
        '25' => 'Приморский край',
        '14' => 'Республика Саха (Якутия)',
        '54' => 'Новосибирская область',
        '10' => 'Республика Карелия',
        '43' => 'Кировская область',
        '59' => 'Пермский край',
        '74' => 'Челябинская область',
        '72' => 'Тюменская область',
        '73' => 'Ульяновская область',
        '57' => 'Орловская область',
        '32' => 'Брянская область',
        '55' => 'Омск и область',
        '66' => 'Свердловская область',
        '58' => 'Пензенская область',
        '56' => 'Оренбургская область',
        '2' => 'Республика Башкортостан',
        '18' => 'Удмуртская Республика',
        '76' => 'Ярославская область',
        '3' => 'Республика Бурятия',
        '75' => 'Забайкальский край',
        '38' => 'Иркутская область',
        '29' => 'Архангельская область',
        '12' => 'Республика Марий Эл',
        '91' => 'Республика Крым',
        '77' => 'Московская область',
        '67' => 'Смоленская область',
        '71' => 'Тульская область',
        '37' => 'Ивановская область',
        '62' => 'Рязанская область',
        '33' => 'Владимирская область',
        '52' => 'Нижегородская область',
        '69' => 'Тверская область',
        '40' => 'Калужская область',
        '48' => 'Липецкая область',
        '68' => 'Тамбовская область',
        '36' => 'Воронежская область',
        '15' => 'Республика Северная Осетия — Алания',
        '8' => 'Республика Калмыкия',
        '21' => 'Чувашская Республика',
        '11' => 'Республика коми',
        '44' => 'Костромская область',
        '24' => 'Красноярский край',
        '17' => 'Республика Тыва',
        '13' => 'Республика Мордовия',
        '86' => 'Ханты-Мансийский автономный округ-Югра',
        '49' => 'Магаданская область',
        '41' => 'Камчатский край',
        '65' => 'Сахалинская область',
        '70' => 'Томская область',
        '78' => 'Санкт-Петербург',
        '53' => 'Новгородская область',
        '28' => 'Амурская область',
        '79' => 'Еврейская автономная область',
        '1' => 'Республика Адыгея',
        '60' => 'Псковская область',
        '6' => 'Республика Ингушетия',
        '89' => 'Ямало-Ненецкий автономный округ',
        '4' => 'Республика Алтай',
    );
}
