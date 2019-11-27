<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:company_name');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$em  = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$output->writeln('--- vidal:company_name started');

		$search      = array('ОБНИНСКАЯ химико-фармацевтическая компания', 'ЦЕНТРАЛЬНО-ЕВРОПЕЙСК', 'ХИМИКО-Ф', 'ХИМИКО-Э', 'ДИАГНОСТИК-ПРЕВЕНТИВ', 'МЕДИКО-БИОЛОГИЧЕСК', 'HOLDING+BONBONSPEZIALITATEN');
		$replacement = array('ОБНИНСКАЯ ХИМИКО - ФАРМАЦЕВТИЧЕСКАЯ КОМПАНИЯ', 'ЦЕНТРАЛЬНО - ЕВРОПЕЙСК', 'ХИМИКО - Ф', 'ХИМИКО-Э', 'ДИАГНОСТИК - ПРЕВЕНТИВ', 'МЕДИКО - БИОЛОГИЧЕСК', 'HOLDING + BONBONSPEZIALITATEN');

		for ($i = 0; $i < count($search); $i++) {
			$pdo->prepare("UPDATE company SET LocalName = REPLACE(LocalName, '{$search[$i]}', '{$replacement[$i]}')")->execute();
		}

		$output->writeln('+++ vidal:company_name completed');
	}
}