<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AllPicturesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('drug:all_pictures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $output->writeln("--- drug:all_pictures");

        $commandNames = array(
            'vidal:product_pictures',
            'vidal:product_picture',
            'vidal:product_main', # Эта команда запустит product_document_all, свой код, product_parent, product_forms, product_pictures
        );

        foreach ($commandNames as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array('command' => $commandName);
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        $output->writeln("+++ drug:all_pictures completed!");
    }
}