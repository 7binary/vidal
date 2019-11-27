<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCountCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:info_count')
			->setDescription('InfoPage.countProducts generator');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:info_command started');

		$em        = $this->getContainer()->get('doctrine')->getManager('drug');
		$infoPages = $em->getRepository('VidalDrugBundle:InfoPage')->findAll();

		# ставим сколько всего у них препаратов
		foreach ($infoPages as $infoPage) {
			$documentIds = $em->getRepository('VidalDrugBundle:Document')->findIdsByInfoPageID($infoPage->getInfoPageID());
			$count       = $em->getRepository('VidalDrugBundle:Product')->countByDocumentIDs($documentIds);
			$infoPage->setCountProducts($count);
		}

		$em->flush();

		$output->writeln('+++ vidal:info_count completed');
	}
}