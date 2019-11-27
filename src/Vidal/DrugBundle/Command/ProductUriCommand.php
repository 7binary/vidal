<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductUriCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_uri');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_uri started');

        /** @var EntityManager $emDefault */
        $emDefault = $this->getContainer()->get('doctrine')->getManager();
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');

        # 1. Выставляем DrugInfo.uri у новых, еще не заполненных
        $products = $this->findProducts($em);
        $emDefault->getRepository("VidalMainBundle:DrugInfo")->createProducts($products);
        $productsWithUri = $emDefault->getRepository("VidalMainBundle:DrugInfo")->findProductIdsWithUri();

        $updateQueryInfo = $emDefault->createQuery("
			UPDATE VidalMainBundle:DrugInfo i
			SET i.uri = :uri
			WHERE i.entityClass = 'Product' AND i.entityId = :ProductID AND i.uri IS NULL
		");
        $total = count($products);

        foreach ($products as $i => $product) {
            $i = $i+1;
            if (false == in_array($product['ProductID'], $productsWithUri)) {
                $uri = empty($product['url'])
                    ? $product['Name'] . '__' . $product['ProductID']
                    : $product['url'];

                $updateQueryInfo->setParameter('uri', $uri)->setParameter('ProductID', $product['ProductID'])->execute();
            }
            if ($i % 10000 == 0 || $i == $total) {
                $output->writeln("... updated DrugInfo.uri: $i / $total");
            }
        }

        # 2. Выставляем Product.uri
        $updateQueryProduct = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.uri = :uri
			WHERE p.ProductID = :ProductID
		');

        $infos = $emDefault->getRepository("VidalMainBundle:DrugInfo")->getProducts();
        $total = count($infos);

        foreach ($infos as $i => $info) {
            $i = $i+1;
            $updateQueryProduct
                ->setParameter('uri', $info['uri'])
                ->setParameter('ProductID', $info['ProductID'])
                ->execute();
            if ($i % 10000 == 0  || $i == $total) {
                $output->writeln("... updated Product.uri: $i / $total");
            }
        }

        $output->writeln("+++ vidal:product_uri completed!");
    }

    private function findProducts(EntityManager $em)
    {
        return $em->createQuery('
			SELECT p.ProductID, p.url, p.Name
			FROM VidalDrugBundle:Product p
			ORDER BY p.ProductID
		')->getResult();
    }
}