<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DropNonRusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:drop_non_rus');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:drop_non_rus');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
        $stmt->execute();

        $this->changeYear($em);

        $pdo->prepare("DELETE FROM document WHERE IsApproved = 0")->execute();
        $pdo->prepare("DELETE FROM product WHERE IsNotForSite = 1")->execute();
        $pdo->prepare("DELETE FROM infopage WHERE CountryEditionCode != 'RUS'")->execute();
        $pdo->prepare("DELETE FROM company WHERE CountryEditionCode != 'RUS'")->execute();
        $pdo->prepare("DELETE FROM product WHERE CountryEditionCode != 'RUS'")->execute();
        $pdo->prepare("UPDATE document SET CountryEditionCode = 'RUS' WHERE CountryEditionCode IS NULL OR CountryEditionCode = ''")->execute();
        $pdo->prepare("DELETE FROM document WHERE CountryEditionCode != 'RUS'")->execute();

        $output->writeln("+++ vidal:drop_non_rus completed!");
    }

    private function changeYear(EntityManager $em)
    {
        // теперь, если у продукта есть документ 17 - го года(или, чтобы алгоритм был универсален, – скорее,
        // ПОСЛЕДУЮЩЕГО за текущим), и НЕТ НИКАКИХ ДРУГИХ(типы 1 и 4 не считаем – т . е . нет других документов
        // типов 2 и 5), выводим его под продуктом и пишем "2017" .

        $year = date('Y');

        $productDocuments = $em->createQuery("
			SELECT pd.ProductID, pd.DocumentID, d.ArticleID, d.YearEdition, d.IsNotForSite
			FROM VidalDrugBundle:ProductDocument pd
			JOIN VidalDrugBundle:Product p WITH p.ProductID = pd.ProductID
			JOIN VidalDrugBundle:Document d WITH d.DocumentID = pd.DocumentID
			WHERE d.ArticleID IN (2,5)
				AND d.YearEdition > $year
			ORDER BY pd.ProductID ASC
		")->getResult();

        $docIds = array();

        foreach ($productDocuments as $pd) {
            if (!empty($pd['DocumentID'])) {
                $pd2016 = $em->createQuery("
                    SELECT pd.ProductID, pd.DocumentID, d.ArticleID, d.YearEdition, d.IsNotForSite
                    FROM VidalDrugBundle:ProductDocument pd
                    JOIN VidalDrugBundle:Product p WITH p.ProductID = pd.ProductID
                    JOIN VidalDrugBundle:Document d WITH d.DocumentID = pd.DocumentID
                    WHERE d.ArticleID IN (2,5)
                        AND d.YearEdition <= $year
                        AND d.DocumentID = :documentID
                    ORDER BY pd.ProductID ASC
                ")->setParameter('documentID', $pd['DocumentID'])
                        ->getResult();

                if (empty($pd2016)) {
                    $docIds[] = $pd['DocumentID'];
                }
            }
        }

        if (!empty($docIds)) {
            $docIds = array_unique($docIds);

            $em->createQuery("
                UPDATE VidalDrugBundle:Document d
                SET d.YearEdition = $year
                WHERE d.DocumentID IN (:docIds)
            ")->setParameter('docIds', $docIds)
                ->execute();
        }
    }
}