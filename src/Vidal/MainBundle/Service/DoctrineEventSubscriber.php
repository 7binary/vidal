<?php

namespace Vidal\MainBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Vidal\MainBundle\Command\BannerAtcCommand;
use Vidal\MainBundle\Command\BannerMkbCommand;
use Vidal\MainBundle\Entity\Banner;
use Vidal\MainBundle\Entity\User;
use Vidal\MainBundle\Entity\AstrazenecaFaq;

class DoctrineEventSubscriber implements EventSubscriber
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Возвращает список имён событий, которые обрабатывает данный класс. Callback-методы должны иметь такие же имена
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'preRemove',
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        /** @var EntityManager $em */
        $em = $args->getEntityManager();

        if ($entity instanceof User) {
            if ($city = $entity->getCity()) {
                if ($region = $city->getRegion()) {
                    $entity->setRegion($region);
                }
                if ($country = $city->getCountry()) {
                    $entity->setCountry($country);
                }
            }
        }
        elseif ($entity instanceof Banner) {
            $maxPosition = $em->createQuery('SELECT MAX(b.position) FROM VidalMainBundle:Banner b')->getSingleScalarResult();
            $maxMobilePosition = $em->createQuery('SELECT MAX(b.mobilePosition) FROM VidalMainBundle:Banner b')->getSingleScalarResult();
            $maxMobileProductPosition = $em->createQuery('SELECT MAX(b.mobileProductPosition) FROM VidalMainBundle:Banner b')->getSingleScalarResult();
            $entity->setPosition($maxPosition ? $maxPosition + 1 : 1);
            $entity->setMobilePosition($maxMobilePosition ? $maxMobilePosition + 1 : 1);
            $entity->setMobileProductPosition($maxMobileProductPosition ? $maxMobileProductPosition + 1 : 1);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof User) {
            if ($city = $entity->getCity()) {
                if ($region = $city->getRegion()) {
                    $entity->setRegion($region);
                }
                if ($country = $city->getCountry()) {
                    $entity->setCountry($country);
                }
            }
        }
        elseif ($entity instanceof AstrazenecaFaq) {
            $answer = $entity->getAnswer();
            empty($answer) ? $entity->setEnabled(0) : $entity->setEnabled(1);
        }
        elseif ($entity instanceof Banner) {
            $emDrug = $this->container->get('doctrine')->getManager('drug');
            # АТХ-коды
            $atc = $entity->getAtc();
            if (!empty($atc)) {
                $atc = explode(';', $atc);
                $atcCodes = array();
                foreach ($atc as $a) {
                    $atcCodes[] = $a;
                    $atcCodes = array_merge($atcCodes, $emDrug->getRepository('VidalDrugBundle:ATC')->findChildren($a));
                }
                $atcCodes = array_unique($atcCodes);
                $products = $emDrug->getRepository("VidalDrugBundle:Product")->findAllByATCCode($atcCodes);
                $productIds = array();
                foreach ($products as $p) {
                    $productIds[] = $p['ProductID'];
                }

                $atcCodes = implode('|', $atcCodes) . '|';
                $productIds = implode('|', $productIds) . '|';
                $atcCodes = '|'.$atcCodes;
                $productIds = '|'.$productIds;

                $entity->setAtcCodes($atcCodes);
                $entity->setProductIds($productIds);

                $command = new BannerAtcCommand();
                $command->setContainer($this->container);
                $input = new ArrayInput(array());
                $output = new NullOutput();
                $command->run($input, $output);
            }
            else {
                $entity->setAtcCodes(null);
                $entity->setProductIds(null);
            }

            # МКБ-10 Нозология
            $nosology = $entity->getNosology();
            if (!empty($nosology)) {
                $nosology = explode(';', $nosology);
                $nosologyCodes = array();
                foreach ($nosology as $n) {
                    $nosologyCodes[] = $n;
                    $children = $emDrug->getRepository('VidalDrugBundle:Nozology')->findChildren($n);
                    $nosologyCodes = array_merge($nosologyCodes, $children);
                }
                $nosologyCodes = array_unique($nosologyCodes);
                $products = $emDrug->getRepository("VidalDrugBundle:Product")->findByNosologies($nosologyCodes);
                $productIds = array();
                foreach ($products as $p) {
                    $productIds[] = $p['ProductID'];
                }

                $nosologyCodes = implode('|', $nosologyCodes) . '|';
                $productIds = implode('|', $productIds) . '|';
                $nosologyCodes = '|'.$nosologyCodes;
                $productIds = '|'.$productIds;

                $entity->setNosologyCodes($nosologyCodes);
                $entity->setNosologyProductIds($productIds);

                $command = new BannerMkbCommand();
                $command->setContainer($this->container);
                $input = new ArrayInput(array());
                $output = new NullOutput();
                $command->run($input, $output);
            }
            else {
                $entity->setNosologyCodes(null);
                $entity->setNosologyProductIds(null);
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof User) {
            /** @var EntityManager $em */
            $em = $args->getEntityManager();
            $pdo = $em->getConnection();
            $pdo->prepare('SET FOREIGN_KEY_CHECKS=0')->execute();
            $pdo->prepare('DELETE FROM user_device WHERE user_id = ' . $entity->getId())->execute();
        }
    }
}