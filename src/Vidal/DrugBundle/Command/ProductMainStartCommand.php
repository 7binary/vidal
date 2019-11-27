<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\KeyValue;

/**
 * Команда выставления основного продукта из группы похожих
 *
 * @package Vidal\DrugBundle\Command
 */
class ProductMainStartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:product_main_start')
            ->setDescription('Starts ProductMainCommand');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:product_main_start');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('default');

        $keyValue = $em->getRepository("VidalMainBundle:KeyValue")->getByKey(KeyValue::START_PRODUCT_MAIN);
        if ($keyValue->getValue() != 'start') {
            $output->writeln('+++ vidal:product_main_start not started @ database');
            return;
        }

        $keyValue->setValue('processing');
        $em->flush($keyValue);

        $this->runCommand((new ProductMainCommand()), $output);
        $keyValue->setValue(null);
        $em->flush($keyValue);

        $output->writeln("+++ vidal:product_main_start");
    }

    private function runCommand(ContainerAwareCommand $command, $output)
    {
        $command->setContainer($this->getContainer());
        $input = new ArrayInput(array());
        $command->run($input, $output);
    }
}