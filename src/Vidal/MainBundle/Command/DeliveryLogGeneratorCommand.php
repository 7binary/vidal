<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use PHPWord_Style_Font;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Art;
use Vidal\DrugBundle\Entity\ArtCategory;
use Vidal\DrugBundle\Entity\ArtRubrique;
use Vidal\MainBundle\Entity\DeliveryLog;

class DeliveryLogGeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:dlg');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:dlg started');

        $container = $this->getContainer();

		/** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $diff = 2539;
        $created = new \DateTime('2017-12-15 00:00:00');
        $uniqueid = 'materia-medica-ran-14-12-2017';

        for ($i = 0; $i < $diff; $i++) {
            $log = new DeliveryLog();
            $log->setUniqueid($uniqueid);
            $log->setCreated($created);
            $log->setFake(true);
            $em->persist($log);
            $em->flush($log);
        }

        $output->writeln('+++ vidal:dlg');
    }
}