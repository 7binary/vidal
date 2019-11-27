<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда выставления SEO-description для продуктов
 *
 * @package Vidal\DrugBundle\Command
 */
class ProductDescriptionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_descriptions')
            ->setDescription('Adds Product.description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $output->writeln('--- vidal:product_descriptions started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();
        $em->createQuery("UPDATE VidalDrugBundle:Product p SET p.description = NULL")->execute();


        $stmt = $pdo->prepare("SELECT RusName2, ProductID, ZipInfo FROM product
          WHERE MarketStatusID IN (1,2,7)
			AND ProductTypeCode NOT IN ('SUBS')
			AND inactive = 0
			AND IsNotForSite = 0
		   ORDER BY ProductID
        ");
        $stmt->execute();
        $products = $stmt->fetchAll();

        # GROUP products
        $productsGrouped = array();
        $productNames = array();

        foreach ($products as $p) {
            $key = $p['ProductID'];
            $name = $p['RusName2'];

            if (!isset($productsGrouped[$key])) {
                $productsGrouped[$key] = array();
            }

            $productsGrouped[$key]['product'] = $p;
            $productsGrouped[$key]['distribs'] = array();

            if (isset($productNames[$name])) {
                $productNames[$name] = $productNames[$name] + 1;
            }
            else {
                $productNames[$name] = 1;
            }
        }

        # GROUP owners
        $companiesMain = $em->createQuery('
			SELECT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property,
				country.RusName Country, pc.ItsMainCompany, p.ProductID, p.RusName2
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			LEFT JOIN VidalDrugBundle:Product p WITH p = pc.ProductID
			WHERE c.inactive = FALSE AND pc.ItsMainCompany = TRUE
		')->getResult();

        foreach ($companiesMain as $c) {
            $key = $c['ProductID'] . '';
            if (!isset($productsGrouped[$key])) {
                continue;
            }
            $productsGrouped[$key]['owner'] = $c;
        }

        # GROUP companies
        $distribs = $em->createQuery('
			SELECT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property,
				country.RusName Country, pc.ItsMainCompany, p.ProductID, p.RusName2
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			LEFT JOIN VidalDrugBundle:Product p WITH p = pc.ProductID
			WHERE c.inactive = FALSE AND pc.ItsMainCompany = FALSE
			ORDER BY pc.Ranking ASC
		')->getResult();

        foreach ($distribs as $d) {
            $key = $d['ProductID'] . '';
            if (!isset($productsGrouped[$key])) {
                continue;
            }
            $productsGrouped[$key]['distribs'][] = $d;
        }

        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.description = :description
			WHERE p.ProductID = :ProductID
		');

        $des = array();
        $i = 0;
        $countProducts = count($productsGrouped);

        foreach ($productsGrouped as $ProductID => &$p) {
            $i++;
            $product = $p['product'];
            $name = $product['RusName2'];
            $description = $this->mb_ucfirst($name) . ', ';
            $zipParts = explode(':', $product['ZipInfo']);
            $description .= $zipParts[0] . '. ';
            $description .=  '('.$product['ProductID'].') ';
            $description .= 'Показания, противопоказания, режим дозирования, побочное действие, передозировка, лекарственное взаимодействие.';

            $description .= ' ' . $p['owner']['LocalName'];

            if ($productNames[$name] > 1 && !empty($p['distribs'])) {
                foreach ($p['distribs'] as $d) {
                    $description .= ', ' . $d['CompanyRusNote'] . ' ' . $d['LocalName'];
                }
            }

            if (isset($des[$description])) {
                $description = $description . ', ' . $p['owner']['Country'];
            }
            if (isset($des[$description]) && isset($p['distribs'][0])) {
                $description = $description . ', ' . $p['distribs'][0]['Country'];
            }
            if (isset($des[$description]) && isset($p['distribs'][1])) {
                $description = $description . ', ' . $p['distribs'][1]['Country'];
            }
            if (isset($des[$description]) && isset($p['distribs'][2])) {
                $description = $description . ', ' . $p['distribs'][2]['Country'];
            }
            if (isset($des[$description]) && isset($p['distribs'][3])) {
                $description = $description . ', ' . $p['distribs'][3]['Country'];
            }

            $description = str_replace('  ', ' ', $description);

            if (!isset($des[$description])) {
                $des[$description] = array();
            }
            $des[$description][] = $ProductID;

            # $output->writeln("... $i / $countProducts");
            $updateQuery->setParameter('description', $description)->setParameter('ProductID', $ProductID)->execute();
        }

//        foreach ($des as $description => $productIds) {
//            if (count($productIds) > 1) {
//                $total += count($productIds);
//                $output->writeln(implode('+', $productIds) . ' = ' . $description);
//            }
//        }
//        var_dump($total);

        $output->writeln("+++ vidal:product_descriptions completed!");
    }

    public function mb_ucfirst($str, $enc = 'utf-8')
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc) . mb_strtolower(mb_substr($str, 1, mb_strlen($str, $enc), $enc), $enc);
    }
}