<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AtcParentCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:atc_parent');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$em = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$output->writeln('--- vidal:atc_parent started');

		$stmt = $pdo->prepare('
			UPDATE atc a1
			LEFT JOIN atc a2 ON a2.ATCCode = a1.ParentATCCode
			SET a1.ParentATCCode = NULL
			WHERE a2.ATCCode IS NULL
		');

		$stmt->execute();

		$output->writeln('+++ vidal:atc_parent completed');
	}
}