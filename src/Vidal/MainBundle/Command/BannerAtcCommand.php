<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

class BannerAtcCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:banner_atc');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:banner_atc started');
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', 0);
        ini_set('memory_limit', -1);

        /** @var Container $container */
        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager('drug');

        $atc = 'G';

        $atcCodes = array_merge([$atc], $em->getRepository('VidalDrugBundle:ATC')->findChildren($atc));
        $products = $em->getRepository("VidalDrugBundle:Product")->findAllByATCCode($atcCodes);

        $ids = array();

        foreach ($products as $p) {
            $ids[] = $p['ProductID'];
        }

        $file = $container->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'banner_atc.txt';
        file_put_contents($file, implode('|', $ids) . '|');

        $output->writeln('+++ vidal:banner_atc completed!');
    }
}
