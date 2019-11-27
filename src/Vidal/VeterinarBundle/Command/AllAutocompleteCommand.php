<?php
namespace Vidal\VeterinarBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда запуска ВСЕХ других команд для ElasticSearch, нужных для обновления базы данных
 */
class AllAutocompleteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('veterinar:all_autocomplete')
            ->setDescription('Runs commands for updated database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $output->writeln("--- veterinar:all_autocomplete");

        $commandNames = array(
            'veterinar:autocomplete_company',
            'veterinar:autocomplete_infopage',
            'veterinar:autocomplete_veterinar',
            'veterinar:autocomplete_veterinar_all',
        );

        foreach ($commandNames as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array('command' => $commandName);
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        $output->writeln("+++ veterinar:all_autocomplete completed!");
    }
}