<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации названий для автодополнения ветеринарных препаратов
 *
 * @package Vidal\DrugBundle\Command
 */
class AutocompleteVeterinarCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:autocomplete_veterinar');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        ini_set('memory_limit', -1);
        $output->writeln('--- veterinar:autocomplete_veterinar started');

        $em    = $this->getContainer()->get('doctrine')->getManager('veterinar');
        $names = array();

        $products = $em->createQuery("
			SELECT p.ProductID, p.RusName
			FROM VidalVeterinarBundle:Product p
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.inactive = FALSE
			ORDER BY p.RusName ASC
		")->getResult();

        $elasticaClient = new \Elastica\Client();
        $elasticaIndex  = $elasticaClient->getIndex('website');
        $elasticaIndex->open();
        $elasticaType   = $elasticaIndex->getType('autocomplete_veterinar');

        # delete if exists
        if ($elasticaType->exists()) {
            $elasticaType->delete();
        }

        # Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);

        # Set mapping
        $mapping->setProperties(array(
            'name' => array('type' => 'string', 'fielddata' => true),
        ));

        # Send mapping to type
        $mapping->send();

        foreach ($products as $product) {
            $names[] = $this->strip($product['RusName']);
        }

        $names = array_values(array_unique($names));

        $total = count($names);
        $id = 0;

        for ($i = 0; $i < $total; $i++) {
            $id   = $i + 1;
            $document = new \Elastica\Document($id, array('name' => $names[$i]));

            $elasticaType->addDocument($document);
            $elasticaType->getIndex()->refresh();
        }

        $output->writeln("+++ veterinar:autocomplete_veterinar loaded $id documents!");
	}

    private function strip($string)
    {
        $pat = array('/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i', '/&amp;/');
        $rep = array('', '', '&');

        $string = mb_strtolower(preg_replace($pat, $rep, $string), 'UTF-8');
        $string = strip_tags($string);

        return $string;
    }
}