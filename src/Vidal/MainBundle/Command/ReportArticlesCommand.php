<?php
namespace Vidal\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SimpleXMLExtended extends \SimpleXMLElement {
	public function addCData($cdata_text) {
		$node = dom_import_simplexml($this);
		$no   = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}

class ReportArticlesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:report_articles')
			->addArgument('numbers', InputArgument::IS_ARRAY, 'Number of year or month');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);

		$em  = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$stmt = $pdo->prepare("
			SELECT r.title rubrique, a.title, a.announce, a.body, a.created, GROUP_CONCAT(n.Code separator ', ') as mkb10
			FROM article a
			LEFT JOIN article_rubrique r ON a.rubrique_id = r.id
			LEFT JOIN article_n an ON an.article_id = a.id
			LEFT JOIN nozology n ON n.NozologyCode = an.NozologyCode
			GROUP BY a.id
			ORDER BY r.title ASC, a.created DESC
		");

		$stmt->execute();
		$articles = $stmt->fetchAll();

		$xml     = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><articles></articles>');

		foreach ($articles as $article) {
			$a = $xml->addChild('article');

			$a->rubrique = $article['rubrique'];
			$a->created = $article['created'];
			$a->mkb10 = $article['mkb10'];

			$a->title = NULL;
			$a->title->addCData($article['title']);
			$a->announce = NULL;
			$a->announce->addCData($article['announce']);
			$a->body = NULL;
			$a->body->addCData($article['body']);
		}

		$file = $this->getContainer()->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..'
			. DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR
			. 'articles.xml';

		# запись в файл
		$xml->asXML($file);

		$output->writeln('+++ vidal:report_articles completed!');
	}
}