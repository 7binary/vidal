<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductUrlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_url');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_url started');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();
        $pdo->prepare("UPDATE product SET url = NULL")->execute();

        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.url = :url
			WHERE p.ProductID = :ProductID
		');

        $products = $this->findProducts($em);
        $usedUrls = array('bioparox');
        $numbers = explode(' ', '1 2 3 4 5 6 7 8 9 10 11 12 13 14 15');

        foreach ($products as $i => $product) {
            $url = $this->transformUrl($product['EngName']);

            if (in_array($url, $usedUrls)) {
                foreach ($numbers as $number) {
                   if (false == in_array($url . '-' . $number, $usedUrls)) {
                       $url = $url . '-' . $number;
                       break;
                   }
                }
            }

            $usedUrls[] = $url;
            $updateQuery->setParameter('url', $url)->setParameter('ProductID', $product['ProductID'])->execute();
        }

        $output->writeln("+++ vidal:product_url completed!");
    }

    private function transformUrl($s)
    {
        $s = str_replace('<SUP>', ' ', $s);
        $s = str_replace('</SUP>', '', $s);
        $s = str_replace('<SUB>', ' ', $s);
        $s = str_replace('</SUB>', '', $s);
        $s = str_replace('<BR/>', ' ', $s);
        $s = str_replace('<BR />', ' ', $s);
        $s = str_replace('<B>', ' ', $s);
        $s = str_replace('</B>', '', $s);
        $s = str_replace('&reg;', '', $s);
        $s = str_replace('&amp;', '', $s);
        $s = str_replace('&trade;', '', $s);
        $s = str_replace('&alpha;', '', $s);
        $s = str_replace('&beta;', '', $s);
        $s = str_replace('&plusmn;', '', $s);
        $s = str_replace('С', 'c', $s);
        $s = str_replace('с', 'c', $s);
        $s = str_replace('М', 'm', $s);
        $s = str_replace('м', 'm', $s);
        $s = str_replace('Т', 't', $s);
        $s = str_replace('т', 't', $s);
        $s = str_replace('Е', 'e', $s);
        $s = str_replace('е', 'e', $s);
        $s = str_replace('Н', 'h', $s);
        $s = str_replace('н', 'h', $s);
        $s = str_replace('В', 'b', $s);
        $s = str_replace('в', 'b', $s);
        $s = str_replace('К', 'k', $s);
        $s = str_replace('к', 'k', $s);
        $s = str_replace('Р', 'p', $s);
        $s = str_replace('Р', 'p', $s);
        $s = str_replace('А', 'a', $s);
        $s = str_replace('а', 'a', $s);
        $s = str_replace('О', 'o', $s);
        $s = str_replace('о', 'o', $s);
        $s = str_replace('(', ' ', $s);
        $s = str_replace(')', ' ', $s);
        $s = str_replace('+', ' ', $s);
        $s = str_replace('№', ' ', $s);
        $s = str_replace('"', '', $s);
        $s = str_replace("'", '', $s);
        $s = str_replace('%', '', $s);
        $s = str_replace('.', ' ', $s);
        $s = str_replace(',', ' ', $s);
        $s = str_replace('/', ' ', $s);
        $s = str_replace(' - ', ' ', $s);
        $s = str_replace('_', ' ', $s);
        $s = str_replace('  ', ' ', $s);

        $s = str_replace(' ', '-', $s);
        $s = str_replace('--', '-', $s);

        $s = strtolower($s);
        $s = trim($s, '-');
        $s = preg_replace('/[^\da-z-]/i', '', $s);

        return $s;
    }

    private function findProducts(EntityManager $em)
    {
        $products = $em->createQuery('
			SELECT p.ProductID
			FROM VidalDrugBundle:Product p
			ORDER BY p.ProductID
		')->getResult();

        $old_ids = file(__DIR__ . '/Data/products.txt', FILE_IGNORE_NEW_LINES);
        $new_ids = array();

        foreach ($products as $product) {
            $new_ids[] = $product['ProductID'] . '';
        }

        $diff_ids = array_values(array_diff($new_ids, $old_ids));
        sort($diff_ids);

        $products = $em->createQuery('
			SELECT p.ProductID, p.EngName
			FROM VidalDrugBundle:Product p
			WHERE p.ProductID IN (:ids)
			ORDER BY p.ProductID
		')->setParameter('ids', $diff_ids)
            ->getResult();

        return $products;
    }
}