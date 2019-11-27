<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации нормальных имен для препаратов
 *
 * @package Vidal\DrugBundle\Command
 */
class DocumentNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:document_name')
			->setDescription('Adds Document.Name');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:document_name started');

		$em  = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$pdo->prepare("UPDATE document SET Name = REPLACE(EngName,'<SUP>','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'</SUP>','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'<SUB>','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'</SUB>','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'<BR/>','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'<BR />','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'&reg;','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'&amp;','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'&trade;','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'&alpha;','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'&beta;','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'&plusmn;','')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,' - ','_')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,' ','_')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'~','_')")->execute();
		$pdo->prepare("UPDATE document SET Name = REPLACE(Name,'__','_')")->execute();
		$pdo->prepare("UPDATE document SET Name = LOWER(Name)")->execute();

		$output->writeln('+++ vidal:document_name completed!');
	}
}