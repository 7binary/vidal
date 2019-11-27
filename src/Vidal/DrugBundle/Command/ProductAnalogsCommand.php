<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Product;

class ProductAnalogsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_analogs')
            ->setDescription('Formats product_analogs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_analogs started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $updateQuery = $em->createQuery("UPDATE VidalDrugBundle:Product p SET p.analogsFull = :analogs WHERE p = :ProductID");

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

            $ProductTypeCode = 'DRUG';
            $EqRateType = 0;
            $ProductID = $product->getProductID();

            $productMolecules = $product->getMolecules();

            $params = array('product' => $product);
            $params['ProductID'] = $ProductID;
            $params['EqRateType'] = $EqRateType;

            $RoaRaw = $em->createQuery("
            SELECT iroa.RouteID
            FROM VidalDrugBundle:ProductItem pri
            JOIN VidalDrugBundle:ProductItemRoute iroa
              WITH iroa.ProductID = pri.ProductID AND iroa.ItemID = pri.ItemID
            WHERE pri.ProductID = :ProductID
        ")->setParameter('ProductID', $ProductID)
                ->getResult();
            $roa = array();

            foreach ($RoaRaw as $r) {
                $roa[] = $r['RouteID'];
            }

            $roaQ = empty($roa) ? '0' : implode(',', $roa);

            # полные аналоги препарата
            if ($EqRateType == 0 || $EqRateType == 4) {
                $stmt = $pdo->prepare("
                SELECT mol.MoleculeID
                FROM product_moleculename pmn
                INNER JOIN moleculename mn ON mn.MoleculeNameID = pmn.MoleculeNameID
                INNER JOIN molecule mol ON mol.MoleculeID = mn.MoleculeID
                WHERE pmn.ProductID = $ProductID
                  AND mol.MoleculeID NOT IN (2203,1144)
            ");
                $stmt->execute();
                $midRaw = $stmt->fetchAll();
                $mid = array();

                foreach ($midRaw as $r) {
                    $mid[] = $r['MoleculeID'];
                }

                $MCount = count($mid);
                if ($MCount == 0 && $EqRateType != 4) {
                    continue;
                }

                $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND (
                    select COUNT(pmn.MoleculeNameID)
                    from product_moleculename pmn
                    inner join moleculename mn on mn.MoleculeNameID = pmn.MoleculeNameID
                    inner join molecule mol on mol.MoleculeID = mn.MoleculeID
                    where pmn.ProductID = pr.ProductID and mol.MoleculeID not in (2203,1144)
                  ) > 0
                  AND pr.molecules = '$productMolecules'
                  AND (
                    select COUNT(*)
                    from product_item pri
                    inner join product_item_route iroa ON iroa.ProductID = pri.ProductID and iroa.ItemID = pri.ItemID
                    where pri.ProductID = pr.ProductID and iroa.RouteID in ($roaQ)
                  ) > 0
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
        }

        $output->writeln("+++ vidal:product_analogs completed!");
    }
}