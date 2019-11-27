<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MoleculeBaseCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:molecule_base');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:molecule_base started');

		$em        = $this->getContainer()->get('doctrine')->getManager('drug');

		$em->createQuery("
			UPDATE VidalDrugBundle:Molecule m
			SET m.MarketStatusID = NULL
			WHERE m.MarketStatusID = 0
		")->execute();

		$output->writeln('+++ vidal:molecule_base completed');
	}
}