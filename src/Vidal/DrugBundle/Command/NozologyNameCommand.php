<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NozologyNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:nozology_name')
			->setDescription('PhThGroups.Name makes first letter uppercase');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:nozology_name started');

		$em        = $this->getContainer()->get('doctrine')->getManager('drug');
		$nozologies = $em->getRepository('VidalDrugBundle:Nozology')->findAll();

		foreach ($nozologies as $nozology) {
			$name = $this->upperFirst($nozology->getName());
			$nozology->setName($name);
		}

		$em->flush();

		$output->writeln('+++ vidal:nozology_name completed');
	}

	private function upperFirst($str)
	{
		return mb_strtoupper(mb_substr($str, 0, 1, 'utf-8'), 'utf-8') . mb_substr($str, 1, 200, 'utf-8');
	}
}