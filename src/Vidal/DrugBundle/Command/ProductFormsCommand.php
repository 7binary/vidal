<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Product;

/**
 * Команда выставления множества описаний
 *
 * @package Vidal\DrugBundle\Command
 */
class ProductFormsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_forms')
            ->addOption('mainProductID', null, InputOption::VALUE_OPTIONAL, 'Specific ProductID', null)
            ->setDescription('Adds Product.forms');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_forms started');

        $mainProductID = $input->getOption('mainProductID');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $pdo->prepare("UPDATE product SET forms = NULL, formsGrouped = NULL, multiForm = 0")->execute();

        if (empty($mainProductID)) {
            $products = $em->createQuery("
                SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatusID, p.ProductID, p.url,
                    p.RusName, p.EngName, p.Name, p.ParentID, p.ga_pageviews, d.DocumentID, d.ArticleID, p.MainID, p.MainIDManual,
                    p.RegistrationNumber, p.RegistrationDate, i.RusName itemName, f.RusName formName,
                    p.NonPrescriptionDrug, p.ProductTypeCode, p.DateOfReRegistration,
                    d.RusName docRusName, d.EngName docEngName
                FROM VidalDrugBundle:Product p
                LEFT JOIN p.document d
                LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
                LEFT JOIN VidalDrugBundle:ProductItem pi WITH pi.ProductID = p.ProductID
                LEFT JOIN VidalDrugBundle:Item i WITH i.ItemID = pi.ItemID
                LEFT JOIN VidalDrugBundle:Form f WITH f.FormID = i.FormID
                WHERE p.MarketStatusID IN (1,2,7)
                    AND p.ProductTypeCode NOT IN ('SUBS')
                    AND p.inactive = FALSE
                    AND p.IsNotForSite = FALSE
                    AND (p.ParentID IS NOT NULL OR p.MainID IS NOT NULL OR p.MainIDManual IS NOT NULL)
                ORDER BY p.RusName ASC
            ")->getResult();
        }
        else {
            $products = $em->createQuery("
                SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatusID, p.ProductID, p.url,
                    p.RusName, p.EngName, p.Name, p.ParentID, p.ga_pageviews, d.DocumentID, d.ArticleID, p.MainID, p.MainIDManual,
                    p.RegistrationNumber, p.RegistrationDate, i.RusName itemName, f.RusName formName,
                    p.NonPrescriptionDrug, p.ProductTypeCode, p.DateOfReRegistration,
                    d.RusName docRusName, d.EngName docEngName
                FROM VidalDrugBundle:Product p
                LEFT JOIN p.document d
                LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
                LEFT JOIN VidalDrugBundle:ProductItem pi WITH pi.ProductID = p.ProductID
                LEFT JOIN VidalDrugBundle:Item i WITH i.ItemID = pi.ItemID
                LEFT JOIN VidalDrugBundle:Form f WITH f.FormID = i.FormID
                WHERE p.MarketStatusID IN (1,2,7)
                    AND p.ProductTypeCode NOT IN ('SUBS')
                    AND p.inactive = FALSE
                    AND p.IsNotForSite = FALSE
                    AND (p.ParentID = :mainId OR p.MainIDManual = :mainId OR p.MainID = :mainId)
                ORDER BY p.RusName ASC
            ")->setParameter('mainId', $mainProductID)
                ->getResult();
        }

        $grouped = array();

        foreach ($products as $p) {
            $key = null;
            if (!empty($p['ParentID'])) {
                $key = $p['ParentID'];
            }
            elseif (!empty($p['MainIDManual'])) {
                $key = $p['MainIDManual'];
            }
            elseif (!empty($p['MainID'])) {
                $key = $p['MainID'];
            }
            else {
                continue;
            }

            if (empty($grouped[$key])) {
                $grouped[$key] = array('products' => array());
            }
            $grouped[$key]['products'][] = $p;
        }

        foreach ($grouped as $product_id => &$data) {
            $products = $data['products'];
            $mainProduct = $em->createQuery("
                SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatusID, p.ProductID, p.url,
                    p.RusName, p.EngName, p.Name, p.ParentID, p.ga_pageviews, d.DocumentID, d.ArticleID, p.MainID, p.MainIDManual,
                    p.RegistrationNumber, p.RegistrationDate, i.RusName itemName, f.RusName formName,
                    p.NonPrescriptionDrug, p.ProductTypeCode, p.DateOfReRegistration,
                    d.RusName docRusName, d.EngName docEngName
                FROM VidalDrugBundle:Product p
                LEFT JOIN p.document d
                LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
                LEFT JOIN VidalDrugBundle:ProductItem pi WITH pi.ProductID = p.ProductID
		        LEFT JOIN VidalDrugBundle:Item i WITH i.ItemID = pi.ItemID
		        LEFT JOIN VidalDrugBundle:Form f WITH f.FormID = i.FormID
                WHERE p.ProductID = $product_id
            ")->setMaxResults(1)->getOneOrNullResult();

            array_unshift($products, $mainProduct);

            $uniqZips = array();
            $uniqZipsGrouped = array();

            foreach ($products as $p) {
                $keyGrouped = empty($p['formName']) ? trim($p['ZipInfo']) : $p['formName'];
                $registrationNumber = trim($p['RegistrationNumber']);
                if($registrationNumber) {
                    $keyGrouped = $keyGrouped.$registrationNumber;
                }

                $key = trim($p['ZipInfo']);

                if (!empty($key) && !isset($uniqZips[$key])) {
                    $uniqZips[$key] = array(
                        'ZipInfo' => empty($p['formName']) ? trim($p['ZipInfo']) : $p['formName'],
                        'MarketStatusID' => $p['MarketStatusID'],
                        'RegistrationDate' => trim($p['RegistrationDate']),
                        'RegistrationNumber' => trim($p['RegistrationNumber']),
                        'DateOfReRegistration' => trim($p['DateOfReRegistration']),
                        'NonPrescriptionDrug' => $p['NonPrescriptionDrug'],
                        'ProductTypeCode' => $p['ProductTypeCode'],
                        'ProductID' => $p['ProductID'],
                        'RusName' => $p['RusName'],
                        'docRusName' => empty($p['docRusName']) ? null : $p['docRusName'],
                        'docEngName' => empty($p['docEngName']) ? null : $p['docEngName'],
                    );
                }

                if (!empty($keyGrouped) && !isset($uniqZipsGrouped[$keyGrouped])) {
                    $uniqZipsGrouped[$keyGrouped] = array(
                        'ZipInfo' => empty($p['formName']) ? trim($p['ZipInfo']) : $p['formName'],
                        'MarketStatusID' => $p['MarketStatusID'],
                        'RegistrationDate' => trim($p['RegistrationDate']),
                        'RegistrationNumber' => trim($p['RegistrationNumber']),
                        'DateOfReRegistration' => trim($p['DateOfReRegistration']),
                        'NonPrescriptionDrug' => $p['NonPrescriptionDrug'],
                        'ProductTypeCode' => $p['ProductTypeCode'],
                        'ProductID' => $p['ProductID'],
                        'RusName' => $p['RusName'],
                        'docRusName' => empty($p['docRusName']) ? null : $p['docRusName'],
                        'docEngName' => empty($p['docEngName']) ? null : $p['docEngName'],
                    );
                }
            }

            $uniqZips = array_values($uniqZips);
            $uniqZipsGrouped = array_values($uniqZipsGrouped);

            # если несколько - то нужно упорядочить по первому числу
            if (count($uniqZips) > 1) {
                usort($uniqZips, function ($a, $b) {
                    preg_match('/\d+/', $a['ZipInfo'], $matches);
                    $number1 = isset($matches[0]) ? $matches[0] : 0;
                    preg_match('/\d+/', $b['ZipInfo'], $matches);
                    $number2 = isset($matches[0]) ? $matches[0] : 0;

                    return $number1 > $number2;
                });
            }

            $data['forms'] = $uniqZips;
            $data['formsGrouped'] = $uniqZipsGrouped;
        }

        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p SET p.forms = :forms WHERE p.ProductID = :ProductID
		');
        $updateQueryGrouped = $em->createQuery('
			UPDATE VidalDrugBundle:Product p SET p.formsGrouped = :formsGrouped WHERE p.ProductID = :ProductID
		');

        foreach ($grouped as $product_id => &$dataGrouped) {
            $forms = json_encode($dataGrouped['forms'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $formsGrouped = json_encode($dataGrouped['formsGrouped'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $updateQuery->setParameter('forms', $forms);
            $updateQuery->setParameter('ProductID', $product_id);
            $updateQuery->execute();

            $updateQueryGrouped->setParameter('formsGrouped', $formsGrouped);
            $updateQueryGrouped->setParameter('ProductID', $product_id);
            $updateQueryGrouped->execute();

            # доп. от Макса. Если у препарата 2 уник названия, то используем документа название в выводе списка
            $uniqueNames = array();
            foreach ($dataGrouped['forms'] as $form) {
                $key = $form['RusName'];
                $uniqueNames[$key] = $form;
            }

            if (count($uniqueNames) > 1) {
                $em->createQuery('
                    UPDATE VidalDrugBundle:Product p
                    SET p.multiForm = TRUE
                    WHERE p.ProductID = :ProductID
                ')->setParameter('ProductID', $product_id)->execute();
            }
        }

        $output->writeln("+++ vidal:product_forms completed!");
    }
}