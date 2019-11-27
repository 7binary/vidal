<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Tag;

class TagTotalCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:tag_total');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- vidal:tag_total started');
		ini_set('memory_limit', -1);

		$em = $this->getContainer()->get('doctrine')->getManager('drug');
		$tags = $em->getRepository('VidalDrugBundle:Tag')->findAll();

		$tagService = $this->getContainer()->get('drug.tag_total');

		foreach ($tags as $tag) {
			$tagService->count($tag->getId());
		}

		$output->writeln('+++ vidal:tag_total completed');
	}
}