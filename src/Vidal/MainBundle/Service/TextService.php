<?php

namespace Vidal\MainBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

class TextService
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container  = $container;
    }

    public function links($text)
    {
        /** @var EntityManager $emDefault */
        $emDefault = $this->container->get('doctrine')->getEntityManager('default');
        /** @var EntityManager $emDrug */
        $emDrug = $this->container->get('doctrine')->getEntityManager('drug');
        $router = $this->container->get('router');

        $lowerText = mb_strtolower($text, 'utf-8');
        $productsPriority = $emDrug->getRepository('VidalDrugBundle:Product')->findByDocumentPriority();
        $excludedProducts = $emDefault->getRepository('VidalMainBundle:Digest')->getExcludedProducts();
        $moleculeNames = $emDrug->getRepository('VidalDrugBundle:Molecule')->findGrouped();

        foreach ($moleculeNames as $name => $molecule) {
            if (in_array($name, $excludedProducts)) {
                continue;
            }
            $pos = mb_strpos($lowerText, $name, null, 'utf-8');
            if ($pos !== false) {
                $url = $router->generate('molecule', array('MoleculeID' => $molecule['MoleculeID']));
                $regex = "/\\b{$name}(а|я|о|е|и|ы|ю|у|ом|ам|ем)?[^-_]\\b/iu";
                $text = preg_replace($regex, '<a href="' . $url . '">${0}</a>', $text);
                $excludedProducts[] = $name;
            }
        }

        foreach ($productsPriority as $RusName => $p) {
            if (in_array($RusName, $excludedProducts)) {
                continue;
            }

            $pos = mb_strpos($lowerText, $RusName, null, 'utf-8');
            if ($pos !== false) {
                $url = $router->generate('product_url', array('EngName' => $p['uri']));
                $regex = "/\\b{$RusName}(а|я|о|е|и|ы|ю|у|ом|ам|ем)?\\b/iu";
                $text = preg_replace($regex, '<a href="' . $url . '">${0}</a>', $text);
            }
        }

        $text = preg_replace('| </a>|iu', '</a> ', $text);

        return $text;
    }
}
