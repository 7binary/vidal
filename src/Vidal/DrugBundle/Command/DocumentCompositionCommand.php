<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда редактирования Document.CompiledComposition
 *
 * @package Vidal\DrugBundle\Command
 */
class DocumentCompositionCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:document_composition')
			->setDescription('Edits compiled composition of document');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:document_composition started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$documents = $em->createQuery('
			SELECT d.DocumentID, d.CompiledComposition
			FROM VidalDrugBundle:Document d
			WHERE d.CompiledComposition LIKE \'%&loz;%\' OR
				d.CompiledComposition LIKE \'%[PRING]%\'
		')->getResult();

		$query = $em->createQuery('
			UPDATE VidalDrugBundle:Document d
			SET d.CompiledComposition = :document_composition
			WHERE d = :document_id
		');

		for ($i = 0; $i < count($documents); $i++) {
			$patterns     = array('/\[PRING\]/i', '/&loz;/i');
			$replacements = array('<i class"pring">Вспомогательные вещества</i>:', '');
			$composition  = preg_replace($patterns, $replacements, $documents[$i]['CompiledComposition']);

			$query->setParameters(array(
				'document_composition' => $composition,
				'document_id'          => $documents[$i]['DocumentID'],
			))->execute();
		}

		$output->writeln('+++ vidal:document_composition completed!');
	}
}