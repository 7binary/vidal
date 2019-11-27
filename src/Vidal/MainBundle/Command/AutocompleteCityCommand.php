<?php
namespace Vidal\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutocompleteCityCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:autocomplete_city');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:autocomplete_city started');

		$em    = $this->getContainer()->get('doctrine')->getManager();
		$names = $em->getRepository('VidalMainBundle:City')->getNames();

		$elasticaClient = new \Elastica\Client();
		$elasticaIndex  = $elasticaClient->getIndex('website');
		$elasticaIndex->open();
		$elasticaType   = $elasticaIndex->getType('autocomplete_city');

		# delete if exists
		if ($elasticaType->exists()) {
			$elasticaType->delete();
		}

		# Define mapping
		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType($elasticaType);

		# Set mapping
		$mapping->setProperties(array(
			'name'  => array('type' => 'string', 'fielddata' => true),
			'title' => array('type' => 'string', 'fielddata' => true),
		));

		# Send mapping to type
		$mapping->send();

		# записываем на сервер документы автодополнения
		$documents = array();

		for ($i = 0; $i < count($names); $i++) {
			$documents[] = new \Elastica\Document(null, array(
				'name'  => mb_strtolower($names[$i]['name'], 'UTF-8'),
				'title' => $names[$i]['title'],
			));

			if ($i && $i % 500 == 0) {
				$elasticaType->addDocuments($documents);
				$elasticaType->getIndex()->refresh();
				$documents = array();
			}
		}
		$elasticaType->addDocuments($documents);
		$elasticaType->getIndex()->refresh();

		$output->writeln("+++ vidal:autocomplete_city loaded $i documents!");
	}
}