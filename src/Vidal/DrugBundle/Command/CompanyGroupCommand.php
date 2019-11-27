<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyGroupCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:company_group');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$output->writeln('--- vidal:company_group started');

		$cgs = $em->getRepository('VidalDrugBundle:CompanyCompanyGroup')->findAll();
		$pdo = $em->getConnection();

		$stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
		$stmt->execute();

		$stmt = $pdo->prepare('UPDATE company SET CompanyGroupID = :CompanyGroupID WHERE CompanyID = :CompanyID');

		foreach ($cgs as $cg) {
			$CompanyGroupID = $cg->getCompanyGroupID();
			$CompanyID      = $cg->getCompanyID();
			$stmt->bindParam(':CompanyGroupID', $CompanyGroupID, \PDO::PARAM_INT);
			$stmt->bindParam(':CompanyID', $CompanyID, \PDO::PARAM_INT);
			$stmt->execute();
		}

		$output->writeln('+++ vidal:company_group completed');
	}
}