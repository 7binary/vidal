<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации автодополнения показаний Nozology
 *
 * @package Vidal\DrugBundle\Command
 */
class AutocompleteNozologyCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:autocomplete_nozology')
			->setDescription('Creates autocomplete_nozology type in Elastica');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:autocomplete_nozology started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$nozologies = $em->getRepository('VidalDrugBundle:Nozology')->findAll();

		$elasticaClient = new \Elastica\Client();
		$elasticaIndex  = $elasticaClient->getIndex('website');
		$elasticaIndex->open();
		$elasticaType   = $elasticaIndex->getType('autocomplete_nozology');

		# Define mapping
		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType($elasticaType);

		# Set mapping
		$mapping->setProperties(array(
			'code' => array('type' => 'string', 'fielddata' => true),
			'name' => array('type' => 'string', 'fielddata' => true),
		));

		# Send mapping to type
		$mapping->send();

		# записываем на сервер документы автодополнения
		$documents = array();

		for ($i = 0; $i < count($nozologies); $i++) {
			$documents[] = new \Elastica\Document($i + 1, array(
				'code' => $nozologies[$i]->getNozologyCode(),
				'name' => mb_strtolower($nozologies[$i]->getName(), 'utf-8'),
			));

			if ($i && $i % 500 == 0) {
				$elasticaType->addDocuments($documents);
				$elasticaType->getIndex()->refresh();
				$documents = array();
			}
		}
		$elasticaType->addDocuments($documents);
		$elasticaType->getIndex()->refresh();

		$output->writeln("+++ vidal:autocomplete_nozology completed!");
	}
}