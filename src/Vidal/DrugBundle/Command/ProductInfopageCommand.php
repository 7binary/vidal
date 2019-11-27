<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductInfopageCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_infopage');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_infopage started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        # генерируем Product.document по связям в таблице ProductDocument
        $productDocuments = $em->createQuery("
			SELECT pd.ProductID, pd.DocumentID, d.ArticleID, d.YearEdition, d.IsNotForSite
			FROM VidalDrugBundle:ProductDocument pd
			JOIN VidalDrugBundle:Product p WITH p.ProductID = pd.ProductID
			JOIN VidalDrugBundle:Document d WITH d.DocumentID = pd.DocumentID
			WHERE d.ArticleID = 3
				AND d.inactive = FALSE
				AND d.IsApproved = TRUE
			ORDER BY pd.ProductID ASC, d.YearEdition DESC, d.DocumentID DESC
		")->getResult();

        foreach ($productDocuments as $pd) {
            $ProductID = $pd['ProductID'];
            $DocumentID = $pd['DocumentID'];

            $infoPages = $em->createQuery("
                SELECT i.InfoPageID 
                FROM VidalDrugBundle:InfoPage i
                JOIN i.documents d WITH d.ArticleID = 3
                WHERE d.DocumentID = :DocumentID
            ")->setParameter('DocumentID', $DocumentID)->getResult();

            for ($i = 0; $i < count($infoPages); $i++) {
                $InfoPageID = $infoPages[$i]['InfoPageID'];
                $stmt = $pdo->prepare("SELECT * FROM product_infopage WHERE ProductID = $ProductID AND InfoPageID = $InfoPageID");
                $stmt->execute();
                $results = $stmt->fetchAll();

                if (empty($results)) {
                    $pdo->prepare("INSERT INTO product_infopage (ProductID, InfoPageID) VALUES ($ProductID, $InfoPageID)")->execute();
                }
            }
        }

        $output->writeln("+++ vidal:product_infopage completed!");
    }
}