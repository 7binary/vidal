<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductSyllablesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:product_syllables');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:product_syllables started');

		$em         = $this->getContainer()->get('doctrine')->getManager('drug');
		$types      = array('p', 'b', 'o');
		$templating = $this->getContainer()->get('templating');

		foreach ($types as $t) {
			list($syllables, $table) = $em->getRepository('VidalDrugBundle:Product')->findByProductType($t);

			# записываем шаблон таблицы со слогами
			$path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'Drugs' . DIRECTORY_SEPARATOR;
			$file = "{$path}products_table_{$t}.html.twig";
			$html = $templating->render('VidalDrugBundle:Drugs:products_table.html.twig', array(
				't'       => $t,
				'table'   => $table,
				'letters' => array_keys($syllables),
			));
			file_put_contents($file, $html);

			# записываем массив со слогами
			$path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR;
			$file = "{$path}syllables_{$t}.json";
			file_put_contents($file, json_encode($syllables));
		}

		$output->writeln('+++ vidal:product_syllables completed!');
	}
}