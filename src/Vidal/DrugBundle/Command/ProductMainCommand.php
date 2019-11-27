<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда выставления основного продукта из группы похожих
 *
 * @package Vidal\DrugBundle\Command
 */
class ProductMainCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_main')
            ->setDescription('Adds Product.MainID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_main started');

        $this->runCommand((new ProductDocumentAllCommand()), $output);

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $pdo->prepare("UPDATE product SET MainID = NULL")->execute();
        $pdo->prepare("UPDATE product SET MainID = MainIDManual WHERE MainIDManual IS NOT NULL")->execute();
        $pdo->prepare("UPDATE product SET document_merge_id = NULL")->execute();

        $productsByDocuments = $em->getRepository('VidalDrugBundle:Product')->productsByDocumentsMain();
        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.MainID = :MainID
			WHERE p.ProductID = :ProductID
		');

        /** @var EntityManager $emDefault */
        $emDefault = $this->getContainer()->get('doctrine')->getManager();
        $productsGaViews = $emDefault->getRepository("VidalMainBundle:DrugInfo")->getProductPageviews();

        foreach ($productsByDocuments as $DocumentID => $products) {
            if (count($products) > 1) {
                # надо найти лучшего по ParentID, либо по посещаемости
                $mainProductID = null;

                foreach ($products as $product) {
                    if (!empty($product['ParentID'])) {
                        $mainProductID = $product['ParentID'];
                        break;
                    }
                }

                foreach ($products as $product) {
                    if (!empty($product['MainID'])) {
                        $mainProductID = $product['MainID'];
                        break;
                    }
                }

                # надо найти лучшего по другим выставленным MainIDManual, учитывая посещаемость препаратов
                if ($mainProductID == null) {
                    $views = 0;
                    foreach ($products as &$product) {
                        $key = $product['ProductID'] . '';
                        $product['ga_pageviews'] = isset($productsGaViews[$key]) ? $productsGaViews[$key] : 0;

                        if ($product['ga_pageviews'] >= $views) {
                            $views = $product['ga_pageviews'];
                            $mainProductID = $product['ProductID'];
                        }
                    }
                }

                foreach ($products as $product) {
                    $ProductID = $product['ProductID'];
                    
                    if ($ProductID != $mainProductID) {
                        $updateQuery->setParameter('MainID', $mainProductID);
                        $updateQuery->setParameter('ProductID', $ProductID);
                        $updateQuery->execute();
                    }
                    else {
                        $updateQuery->setParameter('MainID', null);
                        $updateQuery->setParameter('ProductID', $ProductID);
                        $updateQuery->execute();
                    }

                    $pdo->prepare("UPDATE product SET document_merge_id = $DocumentID WHERE ProductID = $ProductID")->execute();
                }
            }
        }

        # В конце необходимо выставить те идентификаторы, которые проставлялись вручную
        $pdo->prepare("UPDATE product SET MainID = MainIDManual WHERE MainIDManual IS NOT NULL")->execute();

        # А это пересчет связанных с препаратами полей
        $this->runCommand((new ProductParentCommand()), $output);
        $this->runCommand((new ProductFormsCommand()), $output);
        $this->runCommand((new ProductPicturesCommand()), $output);

        $output->writeln("+++ vidal:product_main completed!");
    }

    private function runCommand(ContainerAwareCommand $command, $output)
    {
        $command->setContainer($this->getContainer());
        $input = new ArrayInput(array());
        $command->run($input, $output);
    }
}