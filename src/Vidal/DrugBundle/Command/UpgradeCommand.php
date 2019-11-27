<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class UpgradeCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:upgrade');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:upgrade started');

		$arguments = array('');
		$input     = new ArrayInput($arguments);
		$commands  = $this->getCommands();

		foreach ($commands as $name) {
			$command    = $this->getApplication()->find($name);
			$returnCode = $command->run($input, $output);
		}

		$output->writeln("+++ vidal:upgrade completed!");
	}

	private function getCommands()
	{
		return array(
			'vidal:drop_non_rus',
			'vidal:product_document',

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
			'vidal:product_composition',
			'vidal:product_syllables',
			'vidal:product_zip',
			'vidal:registration_date',

			'vidal:generator_atc',
			'vidal:generator_kfu',
			'vidal:generator_nozology',

			'vidal:nozology_parent',
			'vidal:atc_parent',
			'vidal:kfu_parent',

			'vidal:autocomplete_base',
			'vidal:autocomplete',
			'vidal:autocomplete_document',
			'vidal:autocomplete_ext',
			'vidal:autocomplete_nozology',
			'vidal:autocomplete_pharm',
			'vidal:autocomplete_company',
			'vidal:autocomplete_article',
			'vidal:autocomplete_product',
		);
	}
}