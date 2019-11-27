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
 * Команда запуска ВСЕХ других команд, нужных для обновления базы данных
 */
class AllCommand extends ContainerAwareCommand
{
    /** @var bool Устанавливаем свойство в TRUE для команд, дабы не реагировал DoctrineEventSubscriber */
    public static $db_update = false;

    protected function configure()
    {
        $this->setName('drug:all')
            ->setDescription('Runs commands for updated database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        self::$db_update = true;

        $output->writeln("--- drug:all");

        $commandNames = array(
            'vidal:drop_non_rus',
            'vidal:product_document',
            'vidal:product_document_all',

            'vidal:company_count',
            'vidal:company_country',
            'vidal:company_group',
            'vidal:company_name',

            'vidal:document_clphgrname',
            'vidal:document_composition',
            'vidal:document_image',
            'vidal:document_name',
            'vidal:document_empty',

            'vidal:atc_count',
            'vidal:atc_parent',
            'vidal:info_count',
            'vidal:info_country',
            'vidal:info_tag',
            'vidal:kfu_delete',
            'vidal:kfu_name',
            'vidal:kfu_url',
            'vidal:kfu_count',
            'vidal:molecule_base',
            'vidal:nozology_level',
            'vidal:nozology_class',
            'vidal:nozology_name',
            'vidal:nozology_count',
            'vidal:nozology_code',
            'vidal:pharm_name',
            'vidal:portfolio_document',

            'vidal:product_name',
            'vidal:product_url',
            'vidal:product_uri',
            'vidal:product_composition',
            'vidal:product_syllables',
            'vidal:product_zip',
            'vidal:product_registration_number',
            'vidal:product_parent',
            'vidal:product_children',
            'vidal:product_children_companies',
            'vidal:product_registration_date',
            'vidal:product_description',
            'vidal:product_picture',
            'vidal:product_main', # Эта команда запустит product_document_all, свой код, product_parent, product_forms, product_pictures
            'vidal:product_infopage',
            'vidal:product_atcs', # это для сервиса аналогов препаратов, список атх-кодов
            'vidal:product_molecules', # это для сервиса аналогов препаратов, список молекул

            'vidal:generator_product_letters',
            'vidal:generator_atc',
            'vidal:generator_kfu',
            'vidal:generator_nozology',

            'vidal:nozology_parent',
            'vidal:atc_parent',
            'vidal:kfu_parent',

            'vidal:seo:documen_ul'
        );

        foreach ($commandNames as $commandName) {
            $command = $this->getApplication()->find($commandName);
            $arguments = array('command' => $commandName);
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        $output->writeln("+++ drug:all completed!");
    }
}