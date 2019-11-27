<?php

namespace Vidal\DrugBundle\Command\Analytics;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Analitics;

class AnaliticsProductsCommand extends AnaliticsCommand
{
    protected function configure()
    {
        $this->setName('vidal:analitics:products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);

        $output->writeln('--- vidal:analitics:products started');
        $this->initializeAnalytics();

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $emDrug = $this->getContainer()->get('doctrine')->getManager('drug');

        $startDate = new \DateTime('- 1 year');
        $startDate = $startDate->format('Y-m-d');
        $endDate = new \DateTime('now');
        $endDate = $endDate->format('Y-m-d');

        $em->createQuery("UPDATE VidalMainBundle:DrugInfo i SET i.ga_pageviews = NULL WHERE i.entityClass='Product'")->execute();
        $products = $emDrug->getRepository('VidalDrugBundle:Product')->findGa();
        $em->getRepository("VidalMainBundle:DrugInfo")->createProducts($products);

        $updateQuery = $em->createQuery("
			UPDATE VidalMainBundle:DrugInfo i
			SET i.ga_pageviews = :pageviews, i.ga_from = '$startDate', i.ga_to = '$endDate'
			WHERE i.entityClass = 'Product' AND i.entityId = :ProductID
		");
        $productsData = array();

        foreach ($products as &$product) {
            $href = empty($product['url'])
                ? "/drugs/{$product['Name']}__{$product['ProductID']}"
                : "/drugs/{$product['url']}";
            $product['href'] = $href;
            $productsData[$href] = $product;
        }

        $max = 30;
        $chunked = array_chunk($products, $max);
        $total = count($products);
        $i = 1;

        foreach ($chunked as $chunk) {
            $output->writeln('... ' . ($i * $max) . ' / ' . $total);

            $hrefs = array();

            try {
                foreach ($chunk as $product) {
                    $hrefs[] = str_replace(',', '\,', $product['href']);
                }
                $filters = 'ga:pagePath==' . implode(',ga:pagePath==', $hrefs);

                $result = $this->analytics->data_ga->get($this->analyticsViewId, $startDate, $endDate, $this->metrics, array(
                    'dimensions' => 'ga:pagePath',
                    'filters' => $filters,
                ));

                $rows = $result->getRows();

                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $href = $row[0];
                        $pageviews = $row[1];
                        $product = $productsData[$href];

                        $updateQuery->setParameter('ProductID', $product['ProductID']);
                        $updateQuery->setParameter('pageviews', $pageviews);
                        $updateQuery->execute();
                        usleep(100 * 1000);
                    }
                }
            }
            catch (\Exception $e) {
               // throw $e;
            }

            $i++;
        }

        $output->writeln('+++ vidal:analitics completed');
    }
}