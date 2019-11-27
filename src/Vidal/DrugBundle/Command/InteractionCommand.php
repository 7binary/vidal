<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Interaction;

class InteractionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:interaction');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:interaction started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        /** @var Interaction[] $interactions */
        $interactions = $em->getRepository("VidalDrugBundle:Interaction")->findAll();
        $total = count($interactions);
        $index = 0;

        $container = $this->getContainer();
        /** @var EntityManager $emDefault */
        $emDefault = $container->get('doctrine')->getEntityManager('default');
        /** @var EntityManager $emDrug */
        $emDrug = $container->get('doctrine')->getEntityManager('drug');
        $pdo = $emDrug->getConnection();
        $router = $container->get('router');

        $productsPriority = $emDrug->getRepository('VidalDrugBundle:Product')->findByDocumentPriority();
        $excludedProducts = $emDefault->getRepository('VidalMainBundle:Digest')->getExcludedProducts();
        $moleculeNames = $emDrug->getRepository('VidalDrugBundle:Molecule')->findGrouped();

        foreach ($interactions as $interaction) {
            $usedProducts = array();
            $index++;
            $text = $interaction->getText();
            $output->writeln("... $index / $total");

            $text = preg_replace('#<a ([^>]+)>([^<]+)</a>#', '${2}', $text);
            $interaction->setText($text);

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
            $textLinked = $interaction->getTextLinked();
            if ($textLinked != $text) {
                $id = $interaction->getId();
                $pdo->prepare("UPDATE interaction SET textLinked = ? WHERE id = ?")->execute(array($text, $id));
            }

            $em->flush($interaction);
        }

        $output->writeln('+++ vidal:interaction completed');
    }
}