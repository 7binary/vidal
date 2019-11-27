<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArticleCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:article');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:article started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$articles = $em->createQuery("
			SELECT a
			FROM VidalDrugBundle:Article a
			WHERE a.body LIKE '%href=\"http://www.vidal.ru/%'
		")->getResult();

		foreach ($articles as $article) {
			$body = $article->getBody();
			$body = preg_replace('#href="http://www\.vidal\.ru/#', 'href="/', $body);
			$article->setBody($body);
		}

		$em->flush();

		$output->writeln("+++ vidal:article completed!");
	}
}