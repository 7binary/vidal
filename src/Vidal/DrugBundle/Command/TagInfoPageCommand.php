<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Tag;

class TagInfoPageCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:tag_infopage');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- vidal:tag_infopage started');
		ini_set('memory_limit', -1);

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$tags = $em->createQuery('
			SELECT t
			FROM VidalDrugBundle:Tag t
			WHERE t.infoPage IS NOT NULL
		')->getResult();

		$updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:InfoPage i
			SET i.tag = :tagId
			WHERE i.InfoPageID = :InfoPageID
		');

		foreach ($tags as $tag) {
			$updateQuery
				->setParameter('InfoPageID', $tag->getInfoPage()->getInfoPageID())
				->setParameter('tagId', $tag->getId())
				->execute();
		}

		$output->writeln('+++ vidal:tag_infopage completed');
	}
}