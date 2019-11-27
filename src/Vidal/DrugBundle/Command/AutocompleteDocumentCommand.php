<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации названий для документов (для админки)
 *
 * @package Vidal\DrugBundle\Command
 */
class AutocompleteDocumentCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:autocomplete_document')
			->setDescription('Creates Document name autocomplete in Elastica');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:autocomplete_document started');

		$em    = $this->getContainer()->get('doctrine')->getManager('drug');
		$names = array();

		$documents = $em->createQuery("
			SELECT d.DocumentID, d.RusName
			FROM VidalDrugBundle:Document d
			WHERE d.CountryEditionCode = 'RUS'
			ORDER BY d.RusName ASC
		")->getResult();

		foreach ($documents as $document) {
			$names[] = $document['DocumentID'] . ' ' . $this->strip($document['RusName']);
		}

		$elasticaClient = new \Elastica\Client();
		$elasticaIndex  = $elasticaClient->getIndex('website');
		$elasticaIndex->open();
		$elasticaType   = $elasticaIndex->getType('autocomplete_document');

		# Define mapping
		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType($elasticaType);

		# Set mapping
		$mapping->setProperties(array(
			'name' => array('type' => 'string', 'fielddata' => true),
		));

		# Send mapping to type
		$mapping->send();

		for ($i = 0; $i < count($names); $i++) {
			$document = new \Elastica\Document(null, array('name' => $names[$i]));
			$elasticaType->addDocument($document);
			$elasticaType->getIndex()->refresh();
		}

		$output->writeln("+++ vidal:autocomplete_document completed!");
	}

	private function strip($string)
	{
		$pat = array('/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i', '/&amp;/');
		$rep = array('', '', '&');

		return mb_strtolower(preg_replace($pat, $rep, $string), 'UTF-8');
	}
}