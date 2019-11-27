<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductChildrenCompaniesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_children_companies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_children_companies');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $products = $em->createQuery('
			SELECT p.ProductID, p.ParentID
			FROM VidalDrugBundle:Product p
			WHERE p.ParentID IS NOT NULL
		')->getResult();

        $queryOnePc = $em->createQuery('
			SELECT pc
			FROM VidalDrugBundle:ProductCompany pc
			WHERE pc.ProductID = :ProductID
			  AND pc.CompanyID = :CompanyID
			  AND pc.CompanyRusNote = :CompanyRusNote
		');

        for ($i = 0; $i < count($products); $i++) {
            $p = $products[$i];
            $stmt = $pdo->prepare("SELECT * FROM product_company WHERE ProductID = {$p['ProductID']} AND ItsMainCompany = 0");
            $stmt->execute();
            $pcAll = $stmt->fetchAll();

            foreach ($pcAll as $pc) {
                $parentPc = $queryOnePc
                    ->setParameter('ProductID', $p['ParentID'])
                    ->setParameter('CompanyID', $pc['CompanyID'])
                    ->setParameter('CompanyRusNote', $pc['CompanyRusNote'])
                    ->getArrayResult();

                if (empty($parentPc)) {
                    $pc['id'] = null;
                    $pc['ProductID'] = $p['ParentID'];
                    $pdo->insert('product_company', $pc);
                }
            }
        }

        $output->writeln('+++ vidal:product_children_companies completed!');
    }
}