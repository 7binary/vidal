<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductDocumentAllCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_document_all');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_document_all started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        # перед генерацией обнуляем существующую связь с документом вторичным
        $pdo->prepare("SET FOREIGN_KEY_CHECKS = 0")->execute();
        $pdo->prepare("UPDATE product SET document_all_id = document_id")->execute();

        # там, где в продуктах есть IsWithOutOC - не выводим документы типов 1, 4, 8 (там, где они должны выводиться по правилу, то есть где нет документов типов 2, 5, 6, 7)
        $pdo->prepare("
            DELETE pd
            FROM product_document pd
            INNER JOIN product p ON p.ProductID = pd.ProductID
            INNER JOIN document d ON d.DocumentID = pd.DocumentID
            WHERE p.IsWithoutOC = 1 AND d.ArticleID IN (1, 4, 8)
        ")->execute();

        # генерируем Product.document по связям в таблице ProductDocument
        $productDocuments = $em->createQuery("
			SELECT pd.ProductID, pd.DocumentID, d.ArticleID, d.YearEdition, d.IsNotForSite
			FROM VidalDrugBundle:ProductDocument pd
			JOIN VidalDrugBundle:Product p WITH p.ProductID = pd.ProductID
			JOIN VidalDrugBundle:Document d WITH d.DocumentID = pd.DocumentID
			WHERE d.ArticleID NOT IN (1, 3, 6, 7)
				AND p.ProductTypeCode NOT IN ('BAD','SUBS','SRED', 'MI')
				AND d.inactive = FALSE
				AND d.IsApproved = TRUE
			ORDER BY pd.ProductID ASC, d.YearEdition DESC, d.DocumentID DESC
		")->getResult();

        $grouped = array();

        foreach ($productDocuments as $pd) {
            $key = $pd['ProductID'];
            $grouped[$key][] = $pd;
        }

        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.document_all = :DocumentID
			WHERE p.ProductID = :ProductID
		');

        # далее по приоритету берется документ
        $articlePriority = array(2, 5, 4, 8, 9, 10, 11, 12);
        $i = 0;

        foreach ($grouped as $ProductID => $group) {
            if (count($group) == 1) {
                $DocumentID = $group[0]['DocumentID'];
            }
            else {
                # если 2 документа типа 2 и 5, то выбираем по году
                $d2year = $d5year = null;
                $d2doc = $d5doc = null;

                foreach ($group as $pd) {
                    if ($pd['ArticleID'] == 2) {
                        $d2year = $pd['YearEdition'];
                        $d2doc = $pd['DocumentID'];
                    }
                    elseif ($pd['ArticleID'] == 5) {
                        $d5year = $pd['YearEdition'];
                        $d5doc = $pd['DocumentID'];
                    }
                }

                if ($d2year && $d5year) {
                    $DocumentID = $d2year >= $d5year ? $d2doc : $d5doc;
                }
                else {
                    # если документов несколько, то надо взять один по приоритету Document.ArticleID [2,5,4,3,1]
                    $curr = array_search($group[0]['ArticleID'], $articlePriority);
                    $DocumentID = $group[0]['DocumentID'];

                    foreach ($group as $pd) {
                        $next = array_search($pd['ArticleID'], $articlePriority);
                        if ($next < $curr) {
                            $curr = $next;
                            $DocumentID = $pd['DocumentID'];
                        }
                    }
                }
            }

            $updateQuery->setParameters(array(
                'DocumentID' => $DocumentID,
                'ProductID' => $ProductID,
            ))->execute();

            $i++;
        }

        # для БАДов надо использователь только документ 6
        $productDocuments = $em->createQuery("
			SELECT pd.ProductID, pd.DocumentID
			FROM VidalDrugBundle:ProductDocument pd
			JOIN VidalDrugBundle:Product p WITH p.ProductID = pd.ProductID
			JOIN VidalDrugBundle:Document d WITH d.DocumentID = pd.DocumentID
			WHERE d.ArticleID = 6
				AND p.ProductTypeCode IN ('BAD','SUBS','SRED')
			ORDER BY pd.ProductID ASC
		")->getResult();

        foreach ($productDocuments as $pd) {
            $updateQuery->setParameters(array(
                'DocumentID' => $pd['DocumentID'],
                'ProductID' => $pd['ProductID'],
            ))->execute();
        }

        # для Медицинских Изделий надо использователь только документ 7
        $productDocuments = $em->createQuery("
			SELECT pd.ProductID, pd.DocumentID
			FROM VidalDrugBundle:ProductDocument pd
			JOIN VidalDrugBundle:Product p WITH p.ProductID = pd.ProductID
			JOIN VidalDrugBundle:Document d WITH d.DocumentID = pd.DocumentID
			WHERE d.ArticleID = 7
				AND p.ProductTypeCode IN ('MI')
			ORDER BY pd.ProductID ASC
		")->getResult();

        foreach ($productDocuments as $pd) {
            $updateQuery->setParameters(array(
                'DocumentID' => $pd['DocumentID'],
                'ProductID' => $pd['ProductID'],
            ))->execute();
        }

        # теперь надо установить документ 1 по связи по веществу
        $rawPD = $em->createQuery('
			SELECT DISTINCT p.ProductID, d.DocumentID
			FROM VidalDrugBundle:Product p
			JOIN p.moleculeNames mn
			JOIN mn.MoleculeID m
			JOIN m.documents d
			WHERE p.document is NULL
				AND SIZE(p.moleculeNames) = 1
				AND m.MoleculeID NOT IN (1144,2203)
				AND d.ArticleID = 1
				AND p.IsWithoutOC != 1
		')->getResult();

        $raw = array();
        foreach ($rawPD as $pd) {
            $key = $pd['ProductID'];
            if (!isset($raw[$key])) {
                $raw[$key] = $pd['DocumentID'];
            }
        }

        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.document = :DocumentID
			WHERE p.ProductID = :ProductID
		');

        foreach ($raw as $ProductID => $DocumentID) {
            try {
                $updateQuery->setParameters(array(
                    'ProductID' => $ProductID,
                    'DocumentID' => $DocumentID,
                ))->execute();
            }
            catch (\Exception $e) {
            }
        }

        $pdo->prepare("SET FOREIGN_KEY_CHECKS = 1")->execute();

        $output->writeln('+++ vidal:product_document_all completed!');
    }
}