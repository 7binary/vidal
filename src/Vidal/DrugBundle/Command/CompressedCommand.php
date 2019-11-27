<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompressedCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:compressed');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- vidal:compressed started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();
        $pdo->prepare('ALTER TABLE document ROW_FORMAT=COMPRESSED')->execute();
        $pdo->prepare('ALTER TABLE product ROW_FORMAT=COMPRESSED')->execute();

		$output->writeln('+++ vidal:compressed completed');
	}
}