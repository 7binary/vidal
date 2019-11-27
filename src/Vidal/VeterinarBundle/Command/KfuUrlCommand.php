<?php
namespace Vidal\VeterinarBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KfuUrlCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:kfu_url')
			->setDescription('ClinicoPhPointers.url generator');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('+++ veterinar:kfu_url started');

        /** @var EntityManager $em */
		$em        = $this->getContainer()->get('doctrine')->getManager('veterinar');
		$all = $em->getRepository('VidalVeterinarBundle:ClinicoPhPointers')->findAll();

        $numbers = explode(' ', '1 2 3 4 5 6 7 8 9 10 11 12 13 14 15');
        $em->createQuery('
			UPDATE VidalVeterinarBundle:ClinicoPhPointers c
			SET c.url = NULL
		')->execute();
        $pdo = $em->getConnection();

		foreach ($all as $c) {
			$url = $this->translit($c->getName());

            if ($findByUrl = $em->getRepository('VidalVeterinarBundle:ClinicoPhPointers')->findOneByUrl($url)) {
                foreach ($numbers as $number) {
                    $tryUrl = $url . '_' . $number;
                    if (null == $em->getRepository('VidalVeterinarBundle:ClinicoPhPointers')->findOneByUrl($tryUrl)) {
                        $url = $tryUrl;
                        break;
                    }
                }
            }

            $pdo->prepare("UPDATE clinicophpointers SET url = '$url' WHERE ClPhPointerID = {$c->getClPhPointerID()}")->execute();
        }

		$output->writeln('--- veterinar:kfu_url completed');
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

        $output = str_replace($rus_alphabet, $rus_alphabet_translit, $text);
        $output = str_replace('__', '_', $output);

		return $output;
	}
}