<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Article;

class AutocompleteArticleCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:autocomplete_article')
			->setDescription('Creates autocomplete_article type in Elastica');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:autocomplete_article started');

		$em       = $this->getContainer()->get('doctrine')->getManager('drug');
		/** @var Article[] $articles */
		$articles = $em->getRepository('VidalDrugBundle:Article')->findActive();

        if (count($articles) == 0) {
            $output->writeln("+++ vidal:autocomplete_article skpped - no Articles");
            return;
        }

		$elasticaClient = new \Elastica\Client();
		$elasticaIndex  = $elasticaClient->getIndex('website');
		$elasticaIndex->open();
		$elasticaType   = $elasticaIndex->getType('autocomplete_article');

		# Define mapping
		$mapping = new \Elastica\Type\Mapping();
		$mapping->setType($elasticaType);

		# Set mapping
		$mapping->setProperties(array(
			'title' => array('type' => 'string', 'fielddata' => true),
		));

		# Send mapping to type
		$mapping->send();

		# записываем на сервер документы автодополнения
		$documents = array();

		for ($i = 0; $i < count($articles); $i++) {
			$documents[] = new \Elastica\Document($i + 1, array(
				'title' => mb_strtolower($articles[$i]->getTitle(), 'utf-8'),
			));

			if ($i && $i % 500 == 0) {
				$elasticaType->addDocuments($documents);
				$elasticaType->getIndex()->refresh();
				$documents = array();
			}
		}
		$elasticaType->addDocuments($documents);
		$elasticaType->getIndex()->refresh();

		$output->writeln("+++ vidal:autocomplete_article completed!");
	}
}