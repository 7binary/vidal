<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PharmArticleCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:pharm_article');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:pharm_article started');

		$em  = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$stmt = $pdo->prepare('SELECT id, company_id FROM pharm_article WHERE company_id IS NOT NULL');
		$stmt->execute();
		$results = $stmt->fetchAll();

		$insertStmt = $pdo->prepare('INSERT INTO pharmarticle_pharmcompany (pharmarticle_id, pharmcompany_id) VALUES (?, ?)');

		foreach ($results as $result) {
			$insertStmt->bindParam(1, $result['id']);
			$insertStmt->bindParam(2, $result['company_id']);
			$insertStmt->execute();
		}

		$output->writeln('+++ vidal:pharm_article completed');
	}
}