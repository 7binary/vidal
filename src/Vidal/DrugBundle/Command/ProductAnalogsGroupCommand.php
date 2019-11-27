<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Product;

class ProductAnalogsGroupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_analogs_group')
            ->setDescription('Formats product_analogs_group');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_analogs_group started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();
        $ProductTypeCode = 'DRUG';

        $updateQuery = $em->createQuery("UPDATE VidalDrugBundle:Product p SET p.analogsGroup = :analogs WHERE p = :ProductID");

        $products = $em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
		")->getResult();
        $total = count($products);

        foreach ($products as $i => $product) {
            /** @var Product $product */
            $index = $i + 1;
            if ($i && $i % 1000 == 0) {
                $output->writeln("... $index / $total");
            }

            $ProductID = $product->getProductID();
            $atcs = $product->getAtcs();

            # ATC связи
            $atc3 = array();
            $atc4 = array();
            $atc5 = array();

            foreach ($atcs as $atc) {
                $length = strlen($atc);
                if ($length == 4) {
                    $atc3[] = $atc;
                }
                elseif ($length == 5) {
                    $atc4[] = $atc;
                }
                elseif ($length >= 6) {
                    $atc5[] = $atc;
                }
            }

            $regexp = empty($atc5) ? '---' : implode(' |', $atc5) . ' ';
            $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND pr.atcs REGEXP '$regexp'
                  AND pr.MarketStatusID IN (1,2,7)
                  AND pr.ProductTypeCode NOT IN ('SUBS')
                  AND pr.IsNotForSite = 0
                  AND pr.parent_id IS NULL
                  AND pr.MainID IS NULL
                ORDER BY pr.RusName2 ASC
            ");

            $stmt->execute();
            $products = $stmt->fetchAll();
            $analogs = count($products);
            $updateQuery->setParameter('ProductID', $ProductID)->setParameter('analogs', $analogs)->execute();
        }

        $output->writeln("+++ vidal:product_analogs_group completed!");
    }
}