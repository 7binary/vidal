<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда удаления из ZipInfo препарата символов ромбика &loz;
 *
 * @package Vidal\VeterinarBundle\Command
 */
class ProductZipCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:product_zip')
			->setDescription('Removes &loz; from Product.ZipInfo');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:product_zip started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');
		$pdo = $em->getConnection();

		$products = $em->createQuery('
			SELECT p.ProductID, p.ZipInfo
			FROM VidalVeterinarBundle:Product p
			WHERE p.ZipInfo LIKE \'%&loz;%\'
		')->getResult();

		$query = $em->createQuery('
			UPDATE VidalVeterinarBundle:Product p
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

		$output->writeln('+++ veterinar:product_zip completed!');
	}
}