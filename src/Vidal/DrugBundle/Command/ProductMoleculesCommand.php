<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда редактирования Product.Composition
 *
 * @package Vidal\DrugBundle\Command
 */
class ProductMoleculesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:product_molecules')
			->setDescription('Fills product.molecules');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:product_molecules started');

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();
        $products = array();

        $pdo->prepare("UPDATE product SET molecules = NULL")->execute();

		$stmt = $pdo->prepare("
		    SELECT pmn.ProductID, m.MoleculeID 
		    FROM product_moleculename pmn
		    INNER JOIN moleculename mn ON mn.MoleculeNameID = pmn.MoleculeNameID
		    INNER JOIN molecule m ON mn.MoleculeID = m.MoleculeID
		    ORDER BY pmn.ProductID ASC, m.MoleculeID ASC 
		");

		$stmt->execute();
		$results = $stmt->fetchAll();

		foreach ($results as $row) {
            $key = $row['ProductID'];
            if (!isset($products[$key])) {
                $products[$key] = array();
            }
            $products[$key][] = $row['MoleculeID'];
        }

		$updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.molecules = :molecules
			WHERE p = :product_id
		');

		foreach ($products as $ProductID => $MoleculeIds) {
            $molecules = implode('+', array_unique($MoleculeIds));

            $updateQuery->setParameters(array(
                'molecules' => $molecules,
                'product_id' => $ProductID,
            ))->execute();
        }

		$output->writeln('+++ vidal:product_molecules completed!');
	}
}