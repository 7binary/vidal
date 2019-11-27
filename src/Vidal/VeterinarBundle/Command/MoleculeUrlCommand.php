<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Vidal\VeterinarBundle\Command
 */
class MoleculeUrlCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:molecule_url')
			->setDescription('Adds Molecule.ru');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:molecule_url started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');
		$pdo = $em->getConnection();

		$pdo->prepare("UPDATE molecule SET url = REPLACE(LatName, ' ', '-')")->execute();

        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'</SUP>','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'<SUB>','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'</SUB>','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'<BR/>','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'<BR />','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'&reg;','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'&amp;','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'&trade;','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'&alpha;','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'&beta;','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'&plusmn;','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,' - ','_')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,' ','_')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'__','_')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,'\"','')")->execute();
        $pdo->prepare("UPDATE molecule SET url = REPLACE(url,\"'\",'')")->execute();
        $pdo->prepare("UPDATE molecule SET url = LOWER(url)")->execute();

		$output->writeln("+++ veterinar:molecule_url completed!");
	}
}