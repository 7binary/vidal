<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда редактирования Document.CompiledComposition
 *
 * @package Vidal\VeterinarBundle\Command
 */
class DocumentCompositionCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:document_composition')
			->setDescription('Edits compiled composition of document');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:document_composition started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');

		$documents = $em->createQuery('
			SELECT d.DocumentID, d.CompiledComposition
			FROM VidalVeterinarBundle:Document d
			WHERE d.CompiledComposition LIKE \'%&loz;%\' OR
				d.CompiledComposition LIKE \'%[PRING]%\'
		')->getResult();

		$query = $em->createQuery('
			UPDATE VidalVeterinarBundle:Document d
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

		$output->writeln('+++ veterinar:document_composition completed!');
	}
}