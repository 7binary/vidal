<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PublicationLinkedCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:publication_linked')
            ->addArgument('art_id', InputArgument::OPTIONAL, 'Art.id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:publication_linked started');

        $container = $this->getContainer();
        $art_id = $input->getArgument('art_id');

        /** @var EntityManager $emDefault */
        $emDefault = $container->get('doctrine')->getEntityManager('default');
        /** @var EntityManager $emDrug */
        $emDrug = $container->get('doctrine')->getEntityManager('drug');
        $pdo = $emDrug->getConnection();
        $router = $container->get('router');

        $productsPriority = $emDrug->getRepository('VidalDrugBundle:Product')->findByDocumentPriority();
        $excludedProducts = $emDefault->getRepository('VidalMainBundle:Digest')->getExcludedProducts();
        $moleculeNames = $emDrug->getRepository('VidalDrugBundle:Molecule')->findGrouped();

        # высчитываем для статей энциклопедии

        $models = empty($art_id)
            ? $emDrug->createQuery("SELECT p.id, p.body, p.bodyLinked, p.link FROM VidalDrugBundle:Publication p ORDER BY p.id")->getResult()
            : $emDrug->createQuery("SELECT p.id, p.body, p.bodyLinked, p.link FROM VidalDrugBundle:Publication p WHERE p.id={$art_id} ORDER BY p.id")->getResult();

        if (!empty($models)) {
            $total = count($models);

            foreach ($models as $i => $model) {
                $usedProducts = array();
                $index = $i + 1;
                $output->writeln("... $index / $total");

                $id = $model['id'];
                $text = $model['body'];
                $bodyLinked = $model['bodyLinked'];
                $lowerText = mb_strtolower($text, 'utf-8');

                foreach ($moleculeNames as $name => $molecule) {
                    if (in_array($name, $excludedProducts)) {
                        continue;
                    }
                    $pos = mb_strpos($lowerText, $name, null, 'utf-8');
                    if ($pos !== false) {
                        $url = $router->generate('molecule', array('MoleculeID' => $molecule['MoleculeID']));
                        $regex = "#\\b{$name}(а|я|о|е|и|ы|ю|у|ом|ам|ем)?[^-_]\\b#iu";
                        $text = @preg_replace($regex, '<a href="' . $url . '">${0}</a>', $text);
                        $lowerText = mb_strtolower($text, 'utf-8');
                        $excludedProducts[] = $name;
                    }
                }

                foreach ($productsPriority as $RusName => $p) {
                    if (in_array($RusName, $excludedProducts)) {
                        continue;
                    }

                    $RusName = strip_tags($RusName);
                    $pos = mb_strpos($lowerText, $RusName, null, 'utf-8');
                    if ($pos !== false) {
                        # Более крупное вхождение нельзя заменять мелким впоследствии
/*
                        $used = false;
                        for ($i = 0; $i < count($usedProducts); $i++) {
                            if (strpos($usedProducts[$i], $RusName) !== false) {
                                $used = true;
                            }
                        }
                        if ($used) {
                            continue;
                        }
*/
                        $usedProducts[] = $RusName;

                        $url = $router->generate('product_url', array('EngName' => $p['uri']));
                        
                        $regex = "#\\b{$RusName}(а|я|о|е|и|ы|ю|у|ом|ам|ем)?\\b#iu";
                        $text = @preg_replace($regex, '<a href="' . $url . '">STR_TO_DEL${0}STR_TO_DEL</a>', $text);

                        $lowerText = mb_strtolower($text, 'utf-8');
                    }
                }

                $text = @preg_replace('| </a>|iu', '</a> ', $text);
                $text = str_replace("STR_TO_DEL", "", $text);
                if ($bodyLinked != $text) {
                    $pdo->prepare("UPDATE publication SET bodyLinked = ? WHERE id = ?")->execute(array($text, $id));
                }
            }
        }

        $output->writeln('--- vidal:publication_linked finished');
    }
}