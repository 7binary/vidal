<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KfuNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:kfu_name')
			->setDescription('ClinicoPhPointers.Name makes first letter uppercase');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:kfu_name started');

		/** @var EntityManager $em */
		$em  = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();
		$kfus = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findAll();

		foreach ($kfus as $kfu) {
			$name = $this->upperFirst($kfu->getName());
			$id = $kfu->getId();
            $pdo->prepare("UPDATE clinicophpointers SET name = '$name' WHERE ClPhPointerID = $id")->execute();
		}

		$output->writeln('+++ vidal:kfu_name completed');
	}

	private function upperFirst($str)
	{
		return mb_strtoupper(mb_substr($str, 0, 1, 'utf-8'), 'utf-8') . mb_substr($str, 1, 200, 'utf-8');
	}
}