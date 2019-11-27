<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArticleLinkedCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:article_linked')
            ->addArgument('art_id', InputArgument::OPTIONAL, 'Art.id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:article_linked started');
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

        $articles = empty($art_id)
            ? $emDrug->getRepository("VidalDrugBundle:Article")->findAll()
            : array($emDrug->getRepository("VidalDrugBundle:Article")->findOneById($art_id));

        if (!empty($articles)) {
            $total = count($articles);
            foreach ($articles as $i => $article) {
                $usedProducts = array();
                $index = $i + 1;
                $output->writeln("... $index / $total");

                $text = $article->getBody();
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

                    $pos = mb_strpos($lowerText, $RusName, null, 'utf-8');
                    if ($pos !== false) {
                        # Более крупное вхождение нельзя заменять мелким впоследствии
                        $used = false;
                        for ($i = 0; $i < count($usedProducts); $i++) {
                            if (strpos($usedProducts[$i], $RusName) !== false) {
                                $used = true;
                            }
                        }
                        if ($used) {
                            continue;
                        }
                        $usedProducts[] = $RusName;

                        $url = $router->generate('product_url', array('EngName' => $p['uri']));
                        $regex = "#\\b{$RusName}(а|я|о|е|и|ы|ю|у|ом|ам|ем)?\\b#iu";
                        $text = @preg_replace($regex, '<a href="' . $url . '">${0}</a>', $text);
                        $lowerText = mb_strtolower($text, 'utf-8');
                    }
                }

                $text = @preg_replace('| </a>|iu', '</a> ', $text);
                $bodyLinked = $article->getBodyLinked();
                if ($bodyLinked != $text) {
                    $id = $article->getId();
                    $pdo->prepare("UPDATE article SET bodyLinked = ? WHERE id = ?")->execute(array($text, $id));
                }
            }
        }

        $output->writeln('--- vidal:article_linked finished');
    }
}