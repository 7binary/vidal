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
class ProductParentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_parent')
            ->setDescription('Adds Product.parent_id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_parent started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $pdo->prepare("SET foreign_key_checks=0")->execute();
        $pdo->prepare("UPDATE product SET parent_id = ParentID")->execute();
        $pdo->prepare("UPDATE product SET hasChildrenParentID = 0, hasChildrenMainID = 0")->execute();

        $raw = $em->createQuery("
            SELECT p2.ParentID, p2.MainID
            FROM VidalDrugBundle:Product p2
            WHERE p2.ParentID IS NOT NULL  
              OR p2.MainID IS NOT NULL
        ")->getResult();

        # Выставляем, что у продукта имеются склеенные ParentID
        $ids = array();
        foreach ($raw as $r) {
            if (!empty($r['ParentID'])) {
                $ids[] = $r['ParentID'];
            }
        }

        $em->createQuery("
            UPDATE VidalDrugBundle:Product p
            SET p.hasChildrenParentID = TRUE 
            WHERE p.ProductID IN (:ids)
        ")->setParameter('ids', $ids)->execute();

        # Выставляем, что у продукта имеются склеенные MainID
        $idsMain = array();
        foreach ($raw as $r) {
            if (!empty($r['MainID'])) {
                $idsMain[] = $r['MainID'];
            }
        }

        $em->createQuery("
            UPDATE VidalDrugBundle:Product p
            SET p.hasChildrenMainID = TRUE 
            WHERE p.ProductID IN (:ids)
        ")->setParameter('ids', $idsMain)->execute();

        $output->writeln("+++ vidal:product_parent completed!");
    }
}