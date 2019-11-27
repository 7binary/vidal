<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда удаления из ZipInfo препарата символов ромбика &loz;
 *
 * @package Vidal\DrugBundle\Command
 */
class ProductZipCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:product_zip')
			->setDescription('Removes &loz; from Product.ZipInfo');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:product_zip started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$products = $em->createQuery('
			SELECT p.ProductID, p.ZipInfo
			FROM VidalDrugBundle:Product p
			WHERE p.ZipInfo LIKE \'%&loz;%\'
		')->getResult();

		$query = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.ZipInfo = :product_zip
			WHERE p = :product_id
		');

		for ($i = 0; $i < count($products); $i++) {
			$zip = preg_replace('/&loz;/i', '', $products[$i]['ZipInfo']);

			$query->setParameters(array(
				'product_zip' => $zip,
				'product_id'  => $products[$i]['ProductID'],
			))->execute();
		}

		$output->writeln('+++ vidal:product_zip completed!');
	}
}