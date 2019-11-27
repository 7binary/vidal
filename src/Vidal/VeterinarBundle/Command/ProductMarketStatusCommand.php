<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductMarketStatusCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:product_market_status')
			->setDescription('Sets MarketStatusID = NULL where MarketStatusID = 0');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:product_zip started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');
		$pdo = $em->getConnection();

		$pdo->prepare("UPDATE product SET MarketStatusID = NULL WHERE MarketStatusID = 0")->execute();

		$output->writeln('+++ veterinar:product_market_status completed!');
	}
}