<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:company_name')
			->setDescription('Company.Name generator');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('+++ veterinar:company_name started');
		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');

		$companies = $em->getRepository('VidalVeterinarBundle:Company')->findAll();

		foreach ($companies as $c) {
			$name = $this->translit($c->getLocalName());
			$c->setName($name);
		}

		$em->flush();

		$output->writeln('--- veterinar:company_name completed');
	}

	private function translit($text)
	{
		$text = preg_replace('/&[a-z]+;/', '', $text);
		$text = mb_strtolower($text, 'utf-8');

		// Русский алфавит
		$rus_alphabet = array(
			'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
			'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
			'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
			'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
			'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
			'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
			' ', '.', '(', ')', ',', '/', "'", '"'
		);

		// Английская транслитерация
		$rus_alphabet_translit = array(
			'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'Y',
			'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
			'H', 'TS', 'CH', 'SH', 'SCH', '', 'Y', '', 'E', 'YU', 'IA',
			'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y',
			'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
			'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ia',
			'_', '_', '_', '_', '_', '_', '', ''
		);

		return str_replace($rus_alphabet, $rus_alphabet_translit, $text);
	}
}