<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NozologyCodeCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:nozology_code')
			->setDescription('Fills nozology.NozologyCode2 for tree-sorting');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:nozology_code started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$em->createQuery('
			UPDATE VidalDrugBundle:Nozology n
			SET n.NozologyCode2 = n.NozologyCode
		')->execute();

		$em->createQuery("
			UPDATE VidalDrugBundle:Nozology n
			SET n.NozologyCode2 = 'C00-'
			WHERE n.NozologyCode2 = 'C00*'
		")->execute();

		$output->writeln("+++ vidal:nozology_code completed!");
	}
}