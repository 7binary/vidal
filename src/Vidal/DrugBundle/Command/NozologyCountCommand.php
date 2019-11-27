<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NozologyCountCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:nozology_count');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$output->writeln('--- vidal:nozology_count started');

		$raw         = $em->getRepository('VidalDrugBundle:Nozology')->countProducts();
		$updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Nozology n
			SET n.countProducts = :countProducts
			WHERE n.Code = :Code
		');

		# ставим сколько всего у них препаратов
		for ($i = 0; $i < count($raw); $i++) {
			$updateQuery->setParameters(array(
				'countProducts' => $raw[$i]['countProducts'],
				'Code'          => $raw[$i]['Code'],
			))->execute();
		}

		$output->writeln('+++ vidal:nozology_count completed');
	}
}