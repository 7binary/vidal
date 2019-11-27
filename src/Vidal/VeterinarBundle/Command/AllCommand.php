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
 * Команда запуска ВСЕХ других команд, нужных для обновления базы данных
 */
class AllCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('veterinar:all')
            ->setDescription('Runs commands for updated database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $output->writeln("--- veterinar:all");

        $commandNames = array(
            'veterinar:company_name',
            'veterinar:company_name',
            'veterinar:company_count',
            'veterinar:document_clphgrname',
            'veterinar:document_composition',
            'veterinar:document_name',
            'veterinar:infopage_name',
            'veterinar:info_count',
            'veterinar:product_market_status',
            'veterinar:product_document',
            'veterinar:product_composition',
            'veterinar:product_name',
            'veterinar:product_zip',
            'veterinar:product_registration_date',
            'veterinar:kfu_url',
            'veterinar:kfu_count',
            'veterinar:generator_kfu',
            'veterinar:molecule_url',
        );

        foreach ($commandNames as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array('command' => $commandName);
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        $output->writeln("+++ veterinar:all completed!");
    }
}