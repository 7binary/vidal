<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Vidal\DrugBundle\Command
 */
class ProductPicturesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:product_pictures')
			->setDescription('Formats product.pictures');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:product_pictures started');

        /** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		# Необходимо удалять картинки с датой изменения более 390 дней
        $nowMinusYear = new \DateTime('-390 days');
        $nowMinusYear = $nowMinusYear->format('Y-m-d');
        $currYear = date('Y');

        # находим все родительские связи
        $stmt = $pdo->prepare("SELECT p.ProductID, p.parent_id, p.MainID FROM product p");
        $stmt->execute();
        $results = $stmt->fetchAll();

        $parents = array();
        foreach ($results as $p) {
            $key = $parent = $p['ProductID'];
            if (!empty($p['parent_id'])) {
                $parent = $p['parent_id'];
            }
            elseif (!empty($p['MainID'])) {
                $parent = $p['MainID'];
            }
            $parents[$key] = $parent;
        }

        $pdo->prepare("
            DELETE FROM productpicture
            WHERE DateEditFormatted IS NULL
             OR ((EditionCode != 'SV' OR YearEdition < '$currYear') AND DateEditFormatted < '{$nowMinusYear}')
        ")->execute();

        $pdo->prepare("UPDATE product SET pictures = NULL")->execute();
        $pdo->prepare("UPDATE product SET countPictures = NULL")->execute();

        $stmt = $pdo->prepare("
			SELECT pp.filename, p.ProductID, p.parent_id, p.MainID
			FROM productpicture pp
			INNER JOIN product p ON p.ProductID = pp.ProductID
			WHERE pp.filename IS NOT NULL
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
		");

        $stmt->execute();
        $results = $stmt->fetchAll();
		$products = array();
		$updateQuery = $em->createQuery("UPDATE VidalDrugBundle:Product p SET p.pictures = :pictures, p.countPictures = :countPictures WHERE p.ProductID = :ProductID");

		foreach ($results as $pp) {
            $key = $pp['ProductID'];
			for ($i = 0; $i < 10; $i++) {
                $key = $parents[$key];
            }

			if (!isset($products[$key])) {
				$products[$key] = array();
			}

			$products[$key][] = $pp['filename'];
        }

		foreach ($products as $ProductID => $pictures) {
			$pictures = array_unique($pictures);
            $countPictures = count($pictures);
			$pictures = implode('|', $pictures);
			$updateQuery->setParameter('pictures', $pictures);
			$updateQuery->setParameter('ProductID', $ProductID);
			$updateQuery->setParameter('countPictures', $countPictures);
			$updateQuery->execute();
		}

        $output->writeln("+++ vidal:product_pictures completed!");
	}
}