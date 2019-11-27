<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда редактирования Product.Composition
 *
 * @package Vidal\VeterinarBundle\Command
 */
class ProductCompositionCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:product_composition')
			->setDescription('Edits composition of products');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:product_composition started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');

		$products = $em->createQuery('
			SELECT p.ProductID, p.Composition
			FROM VidalVeterinarBundle:Product p
			WHERE p.Composition LIKE \'%&loz;%\' OR
				p.Composition LIKE \'%[PRING]%\'
		')->getResult();

		$query = $em->createQuery('
			UPDATE VidalVeterinarBundle:Product p
			SET p.Composition = :product_composition
			WHERE p = :product_id
		');

		for ($i = 0; $i < count($products); $i++) {
			$patterns     = array('/\[PRING\]/i', '/&loz;/i');
			$replacements = array('<i class"pring">Вспомогательные вещества</i>:', '');
			$composition  = preg_replace($patterns, $replacements, $products[$i]['Composition']);

			$query->setParameters(array(
				'product_composition' => $composition,
				'product_id'          => $products[$i]['ProductID'],
			))->execute();
		}

		$output->writeln('+++ veterinar:product_composition completed!');
	}
}