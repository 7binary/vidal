<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда выставления основного продукта из группы похожих
 *
 * @package Vidal\DrugBundle\Command
 */
class ProductChildrenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_children');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_children started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $ids = explode(' ', '9842 9843 21669 21670 24368 36535 36549 36551 36552');
        $ids = implode(',', $ids);

        $pdo->prepare("UPDATE product SET parent_id = 24367 WHERE ProductID IN ($ids)")->execute();

        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(ZipInfo, '0', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '1', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '2', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '3', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '4', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '5', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '6', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '7', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '8', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = SUBSTRING_INDEX(shortZipInfo, '9', 1)")->execute();
        $pdo->prepare("UPDATE product SET shortZipInfo = TRIM(shortZipInfo)")->execute();

        # обновляем поле hasChildren
        $pdo->prepare("UPDATE product SET hasChildren = 0")->execute();
        # находим все продукты
        $products = $em->createQuery("
			SELECT p.ProductID, p.ParentID
			FROM VidalDrugBundle:Product p
			WHERE p.ParentID IS NOT NULL
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
		")->getResult();

        foreach ($products as $product) {
            $pdo->prepare("UPDATE product SET hasChildren = 1 WHERE ProductID = {$product['ParentID']}")->execute();
        }

        $output->writeln("+++ vidal:product_children completed!");
    }
}