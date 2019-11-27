<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\User;

class UserPasswordCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:user_password');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:user_password started');

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var User[] $users */
		$users = $em->createQuery('
			SELECT u
			FROM VidalMainBundle:User u
			WHERE u.oldUser = FALSE
		')->getResult();

		foreach ($users as $user) {
		    $user->hashPassword();
		    $em->flush($user);
		}

		$output->writeln('+++ vidal:user_password completed');
	}
}