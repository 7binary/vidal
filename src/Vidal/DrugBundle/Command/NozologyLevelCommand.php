<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NozologyLevelCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:nozology_level');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:nozology_level started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$em->createQuery("
			UPDATE VidalDrugBundle:Nozology n
			SET n.Level = n.Level + 1
			WHERE n.Code LIKE 'C%'
				AND n.Level != 0
		")->execute();

		$em->createQuery("
			UPDATE VidalDrugBundle:Nozology n
			SET n.Level = 1
			WHERE n.Code LIKE 'C%'
				AND n.Level = 1.5
		")->execute();

		$output->writeln('+++ vidal:nozology_level completed');
	}

	private function upperFirst($str)
	{
		return mb_strtoupper(mb_substr($str, 0, 1, 'utf-8'), 'utf-8') . mb_substr($str, 1, 200, 'utf-8');
	}
}