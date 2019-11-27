<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

class SiteUrlTitleCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('site_url_title');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- site_url_title started');

		/** @var Container $container */
		$container = $this->getContainer();
		/** @var EntityManager $em */
		$em = $container->get('doctrine')->getManager('drug');
		/** @var EntityManager $emVet */
		$emVet = $container->get('doctrine')->getManager('veterinar');
		/** @var EntityManager $emDefault */
		$emDefault = $container->get('doctrine')->getManager();
		$webRoot = $container->get('kernel')->getRootDir() . "/../web";

		$lines = array();

		# препараты
		$products = $em->createQuery("
			SELECT p.ProductID, p.Name, p.url, p.EngName, p.RusName2
			FROM VidalDrugBundle:Product p
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
				AND p.ParentID IS NULL
		")->getResult();

		foreach ($products as $p) {
			$url = empty($p['url'])
				? "https://www.vidal.ru/drugs/{$p['Name']}__{$p['ProductID']}"
				: "https://www.vidal.ru/drugs/{$p['url']}";
			$title = $p['RusName2'] . ' (' . $this->strip($p['EngName']) . ')';
			$lines[] = $url . "\t" . $title;
		}

		$fp = fopen($webRoot . '/site_products.txt', 'w');
		foreach ($lines as $line) {
			fwrite($fp, $line . "\n");
		}
		fclose($fp);

		# ВЕТЕРИНАРИЯ - препараты
		$products = $emVet->createQuery("
			SELECT p.ProductID, p.Name, p.RusName
			FROM VidalVeterinarBundle:Product p
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
		")->getResult();

		$lines = array();

		foreach ($products as $p) {
			$url = "https://www.vidal.ru/veterinar/{$p['Name']}-{$p['ProductID']}";
			$title = $this->strip($p['RusName']);
			$lines[] = $url . "\t" . $title;
		}

		$fp = fopen($webRoot . '/site_veterinar_products.txt', 'w');
		foreach ($lines as $line) {
			fwrite($fp, $line . "\n");
		}
		fclose($fp);

		$output->writeln('+++ site_url_title completed');
	}

	private function strip($string)
	{
		$string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));

		return trim(str_replace(explode(' ', '® ™'), '', $string));
	}
}