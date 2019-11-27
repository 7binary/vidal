<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KfuDeleteCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:kfu_delete');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:kfu_delete started');

		$em  = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$pdo->prepare('SET FOREIGN_KEY_CHECKS=0')->execute();
		$pdo->prepare("DELETE FROM clinicophpointers WHERE Code LIKE '50%'")->execute();

		$output->writeln('+++ vidal:kfu_delete completed');
	}
}