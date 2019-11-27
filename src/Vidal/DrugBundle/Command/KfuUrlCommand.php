<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KfuUrlCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:kfu_url')
			->setDescription('ClinicoPhPointers.url generator');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:kfu_url started');

		$em        = $this->getContainer()->get('doctrine')->getManager('drug');
		$companies = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findAll();

		foreach ($companies as $c) {
			$url = $this->translit($c->getName());
			$c->setUrl($url);
		}

		$em->flush();

		$output->writeln('+++ vidal:kfu_url completed');
	}

	private function translit($text)
	{
		$pat  = array('/&[a-z]+;/', '/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i');
		$rep  = array('', '$1', '$1');
		$text = preg_replace($pat, $rep, $text);
		$text = mb_strtolower($text, 'utf-8');

		// Русский алфавит
		$rus_alphabet = array(
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
			'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
			'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
			'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
			'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
			'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
			' ', '.', '(', ')', ',', '/',
		);

		// Английская транслитерация
		$rus_alphabet_translit = array(
			'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'Y',
			'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
			'H', 'TS', 'CH', 'SH', 'SCH', '', 'Y', '', 'E', 'YU', 'IA',
			'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y',
			'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
			'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ia',
			'_', '_', '_', '_', '_', '_'
		);

		return str_replace($rus_alphabet, $rus_alphabet_translit, $text);
	}
}