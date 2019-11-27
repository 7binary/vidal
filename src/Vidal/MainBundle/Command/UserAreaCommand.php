<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserAreaCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:user_area');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:user_area started');

		$container   = $this->getContainer();
		/** @var EntityManager $em */
		$em          = $container->get('doctrine')->getManager();
		$pdo         = $em->getConnection();

		$cityId = 5568853;
		$regionId = 5568685;
		$countryId = 100233;

        $pdo->prepare("UPDATE user SET city_id = $cityId, region_id = $regionId, country_id = $countryId WHERE city_id IS NULL OR country_id IS NULL OR region_id IS NULL")->execute();

		$output->writeln("+++ vidal:user_area completed!");
	}
}