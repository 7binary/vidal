<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutocompleteProductCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:autocomplete_product');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:autocomplete_product started');

		$em    = $this->getContainer()->get('doctrine')->getManager('drug');
		$names = array();

		$products = $em->createQuery("
			SELECT p.ProductID, p.RusName, p.ZipInfo
			FROM VidalDrugBundle:Product p
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode IN ('DRUG','GOME')
				AND p.inactive = FALSE
			ORDER BY p.RusName ASC
		")->getResult();

		foreach ($products as $p) {
			$names[] = $p['ProductID'] . ' ' . $this->strip($p['RusName']);
		}

		$elasticaClient = new \Elastica\Client();
		$elasticaIndex  = $elasticaClient->getIndex('website');
		$elasticaIndex->open();
		$elasticaType   = $elasticaIndex->getType('autocomplete_product');

		# Define mapping
		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType($elasticaType);

		# Set mapping
		$mapping->setProperties(array(
			'name' => array('type' => 'string', 'fielddata' => true),
		));

		# Send mapping to type
		$mapping->send();

		$total = count($products);
		$documents = array();

        for ($i = 0; $i < $total; $i++) {
            $id   = $products[$i]['ProductID'];
            $name = $this->strip($products[$i]['RusName']) . ' ' . $id;
            $documents[] = new \Elastica\Document($id, array('name' => $name));

            if ($i && $i % 500 == 0) {
                $elasticaType->addDocuments($documents);
                $elasticaType->getIndex()->refresh();
                $documents = array();
            }
        }
        if (count($documents)) {
            $elasticaType->addDocuments($documents);
        }
        $elasticaType->getIndex()->refresh();

		$output->writeln("+++ vidal:autocomplete_product loaded $i documents!");
	}

	private function strip($string)
	{
		$pat = array('/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i', '/&amp;/');
		$rep = array('', '', '&');

		return mb_strtolower(preg_replace($pat, $rep, $string), 'UTF-8');
	}
}