<?php

namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации индекса Эластики
 *
 * @package Vidal\DrugBundle\Command
 */
class AutocompleteBaseCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:autocomplete_base')
			->setDescription('Creates website index in Elastica');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:autocomplete_base started');

		$params = array(
            'analysis'           => array(
                'analyzer' => array(
                    'default' => array(
                        'tokenizer' => 'whitespace',
                    ),
                ),
            )
        );

        $client = new \Elastica\Client(array("host" => "localhost", "port" => "9200"));
        $index = $client->getIndex("website");

        if($index->exists()){
            $index->delete();
        }

        $index->create($params);

		$output->writeln('+++ vidal:autocomplete_base completed!');
	}
}