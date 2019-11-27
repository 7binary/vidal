<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации названий для автодополнения поисковой строки
 */
class AutocompleteInfoPageCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:autocomplete_infopage')
			->setDescription('Creates autocomplete for InfoPage.RusName in Elastica');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- veterinar:autocomplete_infopage started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');
		$names = $em->getRepository('VidalVeterinarBundle:InfoPage')->getNames();

        $names = array_unique($names);
        sort($names);

        $elasticaClient = new \Elastica\Client();
        $elasticaIndex  = $elasticaClient->getIndex('website');
        $elasticaIndex->open();
        $elasticaType   = $elasticaIndex->getType('vai');

        if ($elasticaType->exists()) {
            $elasticaType->delete();
        }

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);

        // Set mapping
        $mapping->setProperties(array(
            'id'   => array('type' => 'integer'),
            'name' => array('type' => 'string', 'fielddata' => true),
        ));

        // Send mapping to type
        $mapping->send();

        # записываем на сервер документы автодополнения
        $documents = array();

        for ($i = 0; $i < count($names); $i++) {
            $documents[] = new \Elastica\Document($i + 1, array('name' => $names[$i]));

            if ($i && $i % 500 == 0) {
                $elasticaType->addDocuments($documents);
                $elasticaType->getIndex()->refresh();
                $documents = array();
            }
        }

        $elasticaType->addDocuments($documents);
        $elasticaType->getIndex()->refresh();

        $output->writeln("+++ veterinar:autocomplete_infopage loaded $i documents!");
	}
}