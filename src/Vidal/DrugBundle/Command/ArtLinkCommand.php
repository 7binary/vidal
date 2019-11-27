<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArtLinkCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:art_link');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:art_link started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$arts = $em->createQuery("
			SELECT a
			FROM VidalDrugBundle:Art a
			WHERE a.link IS NULL
			 	OR a.link = ''
		")->getResult();

		foreach ($arts as $art) {
			$title = $art->getTitle();
			$art->setLink($this->generateLink($title));
		}

		$em->flush();

		$output->writeln("+++ vidal:art_link completed!");
	}

	protected function generateLink($title)
	{
		$rus = array(
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
			'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
			'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
			'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
			'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
			'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
			'—', '-', '  ', ',', '.', '!', '?', '/',
		);

		$eng = array(
			'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'Y',
			'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
			'H', 'TS', 'CH', 'SH', 'SCH', '', 'Y', '', 'E', 'YU', 'IA',
			'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y',
			'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
			'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ia',
			'_', '_', '_', '_', '_', '_', '_', '_',
		);

		$title = str_replace($rus, $eng, $title);
		$title = mb_strtolower($title, 'utf-8');

		return $title;
	}
}