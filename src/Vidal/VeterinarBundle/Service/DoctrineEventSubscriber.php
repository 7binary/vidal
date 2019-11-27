<?php

namespace Vidal\VeterinarBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Vidal\VeterinarBundle\Entity\InfoPage;
use Vidal\VeterinarBundle\Entity\Product;

class DoctrineEventSubscriber implements EventSubscriber
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        try {
            $entity = $args->getEntity();

            if ($entity instanceof InfoPage) {
                $this->updateInfopage($args);
            }

            if ($entity instanceof Product) {
                $this->updateProductNames($args);
            }
        }
        catch (\Exception $e) {
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        try {
            $entity = $args->getEntity();

            if ($entity instanceof InfoPage) {
                $this->updateInfopage($args);
            }
        }
        catch (\Exception $e) {
        }
    }

    private function updateProductNames(LifecycleEventArgs $args)
    {
        /** @var EntityManager $em */
        $em = $args->getEntityManager();
        $pdo = $em->getConnection();
        /** @var Product $product */
        $product = $args->getObject();
        $ProductID = $product->getProductID();

        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName,'<SUP>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</SUP>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<SUB>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</SUB>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<BR/>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<BR />','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&reg;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&amp;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&trade;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&alpha;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&beta;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&plusmn;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'  ',' ') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'\"','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'%','') WHERE ProductID = $ProductID")->execute();
    }

    private function updateInfopage(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        $infoPages = $em->getRepository('VidalVeterinarBundle:InfoPage')->findAll();

        foreach ($infoPages as $i) {
            $name = $this->translit($i->getRusName());
            $i->setName($name);
        }

        $em->flush();
    }

    private function translit($text)
    {
        $text = mb_strtolower($text, 'utf-8');

        // Русский алфавит
        $rus_alphabet = array(
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
            'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
            'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
            'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            ' ', '.', '(', ')', ',',
        );

        // Английская транслитерация
        $rus_alphabet_translit = array(
            'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'Y',
            'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
            'H', 'TS', 'CH', 'SH', 'SCH', '', 'Y', '', 'E', 'YU', 'IA',
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y',
            'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
            'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ia',
            '_', '_', '_', '_', '_',
        );

        return str_replace($rus_alphabet, $rus_alphabet_translit, $text);
    }
}