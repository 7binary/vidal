<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Vidal\DrugBundle\Entity\Document;
use Vidal\DrugBundle\Entity\Nozology;
use Vidal\DrugBundle\Entity\Product;
use Vidal\MainBundle\Entity\Delivery;

class BannerMkbCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:banner_mkb');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:banner_mkb started');
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set('memory_limit', -1);

        /** @var Container $container */
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager('drug');
        $ProductID = '1664';

        /** @var Product $product */
        $product = $em->getRepository("VidalDrugBundle:Product")->findOneByProductID($ProductID);
        /** @var Document $document */
        $document = $product->getDocument();
        /** @var Nozology[] $nosologyCodes */
        $nosologyCodes = $em->getRepository("VidalDrugBundle:Nozology")->findByDocumentId($document->getDocumentID());
        $codes = [];

        foreach ($nosologyCodes as $code) {
           $codes = array_merge($codes, $em->getRepository("VidalDrugBundle:Nozology")->findChildren($code['NozologyCode']));
        }

        $products = $em->getRepository("VidalDrugBundle:Product")->findByNosologies($codes);
        $ids = array();

        foreach ($products as $p) {
            $ids[] = $p['ProductID'];
        }

        $file = $container->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'banner_mkb.json';
        file_put_contents($file, json_encode(array_unique($ids)));

        $output->writeln('+++ vidal:banner_mkb completed!');
    }
}
