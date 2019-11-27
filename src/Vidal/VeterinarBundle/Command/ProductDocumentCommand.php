<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductDocumentCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:product_document');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- veterinar:product_document started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');

		# перед генерацией обнуляем существующую связь с документом
		$em->createQuery('
			UPDATE VidalVeterinarBundle:Product p
			SET p.document = NULL
		')->execute();

		$articlePriorities = array(1, 3, 4, 5, 2, 6, 7, 8, 9, 10, 11,12,13,14,15);

		# генерируем Product.document по связям в таблице ProductDocument
		$productDocuments = $em->createQuery("
			SELECT pd.ProductID, pd.DocumentID, pd.Ranking, d.ArticleID
			FROM VidalVeterinarBundle:ProductDocument pd
			JOIN VidalVeterinarBundle:Product p WITH p.ProductID = pd.ProductID
			JOIN VidalVeterinarBundle:Document d WITH d.DocumentID = pd.DocumentID
			ORDER BY pd.Ranking ASC
		")->getResult();

		$updateQuery = $em->createQuery('
			UPDATE VidalVeterinarBundle:Product p
			SET p.document = :DocumentID
			WHERE p.ProductID = :ProductID
		');

		foreach ($articlePriorities as $ArticleID) {
			foreach ($productDocuments as $pd) {
				if ($pd['ArticleID'] == $ArticleID) {
					$updateQuery->setParameters(array(
						'DocumentID' => $pd['DocumentID'],
						'ProductID'  => $pd['ProductID'],
					))->execute();
				}
			}
		}

		$output->writeln('+++ veterinar:product_document completed!');
	}
}