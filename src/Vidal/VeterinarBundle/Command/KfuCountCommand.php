<?php
namespace Vidal\VeterinarBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KfuCountCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:kfu_count');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        /** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');

		$output->writeln('--- veterinar:kfu_count started');

		$raw         = $em->getRepository('VidalVeterinarBundle:ClinicoPhPointers')->countProducts();
		$updateQuery = $em->createQuery('
			UPDATE VidalVeterinarBundle:ClinicoPhPointers c
			SET c.countProducts = :countProducts
			WHERE c.ClPhPointerID= :ClPhPointerID
		');

		# ставим сколько всего у них препаратов
		for ($i = 0; $i < count($raw); $i++) {
			$updateQuery->setParameters(array(
				'countProducts' => $raw[$i]['countProducts'],
				'ClPhPointerID' => $raw[$i]['ClPhPointerID'],
			))->execute();
		}

		$output->writeln('+++ veterinar:kfu_count completed');
	}
}