<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PharmNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:pharm_name')
			->setDescription('PhThGroups.Name makes first letter uppercase');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:pharm_name started');

		$em        = $this->getContainer()->get('doctrine')->getManager('drug');
		$pharms = $em->getRepository('VidalDrugBundle:PhThGroups')->findAll();

		foreach ($pharms as $pharm) {
			$name = $this->upperFirst($pharm->getName());
			$pharm->setName($name);
		}

		$em->flush();

		$output->writeln('+++ vidal:pharm_name completed');
	}

	private function upperFirst($str)
	{
		return mb_strtoupper(mb_substr($str, 0, 1, 'utf-8'), 'utf-8') . mb_substr($str, 1, 200, 'utf-8');
	}
}