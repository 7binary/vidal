<?php
namespace Vidal\DrugBundle\Command\Analytics;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Analitics;

class AnaliticsCommand extends ContainerAwareCommand
{
    /** @var \Google_Client */
    protected $client;
    /** @var \Google_Service_Analytics */
    protected $analytics;

    protected $analyticsViewId = 'ga:78472229';

    protected $metrics = 'ga:pageviews'; // 'ga:visits,ga:pageviews,ga:bounces,ga:entranceBounceRate,ga:visitBounceRate';

    protected function configure()
    {
        $this->setName('vidal:analitics');
    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
	    set_time_limit(0);
		ini_set('memory_limit', -1);

		$output->writeln('--- vidal:analitics started');
        $this->initializeAnalytics();

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');

        /** @var Analitics $analitics */
        $analitics = $em->getRepository("VidalDrugBundle:Analitics")->get();

        if ($analitics->getProcess() == false) {
            $output->writeln('+++ vidal:analitics not processing');
            return;
        }

        $now = new \DateTime('now');
        $analitics->setDateLast($now);
        $analitics->setProcess(false);
        $em->flush($analitics);

        $startDate = $analitics->getDateFrom()->format('Y-m-d');
        $endDate = $analitics->getDateTo()->format('Y-m-d');

        $em->createQuery("UPDATE VidalDrugBundle:Product p SET p.ga_pageviews = NULL")->execute();
        $products = $em->getRepository('VidalDrugBundle:Product')->findGa();

        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.ga_pageviews = :pageviews
			WHERE p.ProductID = :ProductID
		');
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
            }

            $i++;
        }

		$output->writeln('+++ vidal:analitics completed');
	}

    protected function initializeAnalytics()
    {
        $key = $this->getContainer()->get('kernel')->getRootDir() . '/../src/Vidal/DrugBundle/Command/Analytics/ga.json';

        $client = new \Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($key);
        $client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
        $client->addScope('https://www.googleapis.com/auth/webmasters.readonly');
        $client->addScope('https://www.googleapis.com/auth/webmasters');

        $guzzleClient = new \GuzzleHttp\Client(array('verify' => false));
        $client->setHttpClient($guzzleClient);

        $this->client = $client;
        $this->analytics = new \Google_Service_Analytics($this->client);
    }
}