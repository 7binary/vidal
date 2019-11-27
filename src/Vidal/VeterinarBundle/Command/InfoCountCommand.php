<?php
namespace Vidal\VeterinarBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCountCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:info_count')
			->setDescription('InfoPage.countProducts generator');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- veterinar:info_count started');

        /** @var EntityManager $em */
		$em        = $this->getContainer()->get('doctrine')->getManager('veterinar');
		$infoPages = $em->getRepository('VidalVeterinarBundle:InfoPage')->findAll();
        $pdo = $em->getConnection();

		# ставим сколько всего у них препаратов
		foreach ($infoPages as $infoPage) {
			$documentIds = $em->getRepository('VidalVeterinarBundle:Document')->findIdsByInfoPageID($infoPage->getInfoPageID());
			$count       = $em->getRepository('VidalVeterinarBundle:Product')->countByDocumentIDs($documentIds);
            $pdo->prepare("UPDATE infopage SET countProducts = $count WHERE InfoPageID = {$infoPage->getInfoPageID()}")->execute();
			//$d = implode('-', $documentIds); echo " [$d, count=$count, InfoPageId={$infoPage->getInfoPageID()} ] ";
		}

		$output->writeln('+++ veterinar:info_count completed');
	}
}