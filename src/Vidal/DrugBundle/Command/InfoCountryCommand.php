<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCountryCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:info_country')
			->setDescription('InfoPage.countProducts generator');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:info_country started');

		$em        = $this->getContainer()->get('doctrine')->getManager('drug');

		$em->createQuery("
			UPDATE VidalDrugBundle:InfoPage i
			SET i.CountryCode = NULL
			WHERE i.CountryCode = ''
		")->execute();

		$output->writeln('+++ vidal:info_country completed');
	}
}