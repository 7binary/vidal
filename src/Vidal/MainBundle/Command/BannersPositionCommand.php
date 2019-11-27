<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\Banner;

class BannersPositionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:banners_position');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:banners_position started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var Banner[] $banners */
        $banners = $em->createQuery("
                SELECT b
                FROM VidalMainBundle:Banner b
                ORDER BY b.position ASC, b.id ASC
            ")->getResult();

        for ($i = 0; $i < count($banners); $i++) {
            $position = $i + 1;
            $banners[$i]->setPosition($position);
            $em->flush($banners[$i]);
        }

        $banners = $em->createQuery("
                SELECT b
                FROM VidalMainBundle:Banner b
                ORDER BY b.mobilePosition ASC, b.id ASC
            ")->getResult();

        for ($i = 0; $i < count($banners); $i++) {
            $position = $i + 1;
            $banners[$i]->setMobilePosition($position);
            $em->flush($banners[$i]);
        }

        $banners = $em->createQuery("
                SELECT b
                FROM VidalMainBundle:Banner b
                ORDER BY b.mobileProductPosition ASC, b.id ASC
            ")->getResult();

        for ($i = 0; $i < count($banners); $i++) {
            $position = $i + 1;
            $banners[$i]->setMobileProductPosition($position);
            $em->flush($banners[$i]);
        }

        $output->writeln("+++ vidal:banners_position completed!");
    }
}