<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AllAutocompleteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('drug:all_autocomplete');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $output->writeln("--- drug:all_autocomplete");

        $commandNames = array(
            'vidal:autocomplete_base',
            'vidal:autocomplete',
            'vidal:autocomplete_document',
            'vidal:autocomplete_ext',
            'vidal:autocomplete_nozology',
            'vidal:autocomplete_pharm',
            'vidal:autocomplete_company',
            'vidal:autocomplete_article',
            'vidal:autocomplete_product',
            'vidal:autocomplete_city',
        );

        foreach ($commandNames as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array('command' => $commandName);
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        $output->writeln("+++ drug:all_autocomplete completed!");
    }
}