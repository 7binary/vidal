<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PortfolioDocumentCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:portfolio_document');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:portfolio_document started');

		$em  = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$stmt = $pdo->prepare('
			SELECT pp.id, pp.title, pp.DocumentID
			FROM pharm_portfolio pp
			LEFT JOIN document d ON d.DocumentID = pp.DocumentID
			WHERE d.DocumentID IS NULL
		');

		$stmt->execute();
		$portfolios = $stmt->fetchAll();

		foreach ($portfolios as $portfolio) {
			# пытаемся найти документ по имени (title)
			$document = $em->createQuery('
				SELECT d.DocumentID
				FROM VidalDrugBundle:Document d
				WHERE d.RusName = :title
			')->setParameter('title', $portfolio['title'])
				->setMaxResults(1)
				->getOneOrNullResult();

			if ($document) {
				$em->createQuery('
					UPDATE VidalDrugBundle:PharmPortfolio p
					SET p.DocumentID = :DocumentID
					WHERE p.id = :id
				')->setParameters(array(
					'DocumentID' => $document['DocumentID'],
					'id'         => $portfolio['id'],
				))->execute();
			}
			else {
				$em->createQuery('
					UPDATE VidalDrugBundle:PharmPortfolio p
					SET p.DocumentID = NULL
					WHERE p.id = :id
				')->setParameter('id', $portfolio['id'])
					->execute();
			}
		}

		$output->writeln("+++ vidal:portfolio_document completed!");
	}
}