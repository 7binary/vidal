<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда запуска ВСЕХ других команд по пересчету активных препаратов (countProducts)
 */
class AllCountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('drug:all_count')
            ->setDescription('Runs commands to fill countProducts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $output->writeln("--- drug:all");

        $commandNames = array(
            'vidal:company_count',
            'vidal:atc_count',
            'vidal:kfu_count',
            'vidal:nozology_count',
        );

        foreach ($commandNames as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array('command' => $commandName);
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        $output->writeln("+++ drug:all_count completed!");
    }
}