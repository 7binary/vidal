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
class ProductAtcsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:product_atcs')
			->setDescription('Fills product.atcs');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:product_atcs started');

        /** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

        $pdo->prepare("UPDATE product SET atcs = NULL")->execute();

        $stmt = $pdo->prepare("
			SELECT p.ProductID, atc.ATCCode atc1, atc2.ATCCode atc2, atc3.ATCCode atc3
			FROM product_atc pa
			INNER JOIN product p ON p.ProductID = pa.ProductID
			INNER JOIN atc atc ON atc.ATCCode = pa.ATCCode AND atc.Level IN (3,4,5)
			LEFT JOIN document d ON d.DocumentID = p.document_id
			LEFT JOIN atc atc2 ON atc2.ATCCode = atc.ParentATCCode AND atc2.Level IN (3,4,5)
			LEFT JOIN atc atc3 ON atc3.ATCCode = atc2.ParentATCCode AND atc3.Level IN (3,4,5)
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
			ORDER BY ProductID, atc.ATCCode	
		");

        $stmt->execute();
        $results = $stmt->fetchAll();

        $products = array();

        foreach ($results as $r) {
            $key = $r['ProductID'];
            if (!isset($products[$key])) {
                $products[$key] = array();
            }
            $products[$key][] = $r['atc1'];
            if (!empty($r['atc2'])) {
                $products[$key][] = $r['atc2'];
            }
            if (!empty($r['atc3'])) {
                $products[$key][] = $r['atc3'];
            }
        }

        foreach ($products as $ProductID => $atcs) {
            $atcs = array_unique($atcs);
            $atcsStr = '';
            foreach ($atcs as $atc) {
                $atcsStr .= $atc . ' ';
            }
            $pdo->prepare("UPDATE product SET atcs = '$atcsStr' WHERE ProductID = $ProductID")->execute();
        }

        $output->writeln("+++ vidal:product_atcs completed!");
	}
}