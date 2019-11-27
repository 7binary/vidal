<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Vidal\MainBundle\Entity\ProtecPrice;
use Vidal\MainBundle\Entity\ProtecProduct;
use Vidal\MainBundle\Entity\ProtecRegion;
use Vidal\MainBundle\Service\FtpImplicitSsl;
use Vidal\MainBundle\Service\ImplicitFtp;

class CidrCommand extends ContainerAwareCommand
{
    protected $regions = array();

    protected function configure()
    {
        $this->setName('vidal:cidr');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:cidr started');

        $csvFile = file(__DIR__ . '/Data/cidr.txt');
        foreach ($csvFile as $line) {
            $line = mb_convert_encoding($line, "utf-8", "windows-1251");
            $data = str_getcsv($line, "\t");

            if (!empty($data[5])) {
                $title = $data[5];
                $this->regions[$title] = $title;
            }
        }

        foreach ($this->regions as $t => $t) {
            echo $t . PHP_EOL;
        }

        $output->writeln('+++ vidal:cidr completed!');
    }
}
