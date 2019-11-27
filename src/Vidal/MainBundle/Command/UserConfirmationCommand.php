<?php
namespace Vidal\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\User;

class UserConfirmationCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:user:confirmation')
			->setDescription('Resend email to users to confirm registration');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:user:confirmation started');

		$container    = $container = $this->getContainer();
		$emailService = $container->get('email.service');
		$em           = $container->get('doctrine')->getManager();

		# получаем список адресов по рассылке
        /** @var User[] $users */
		$users = $em->createQuery("
			SELECT u
			FROM VidalMainBundle:User u
			WHERE u.username = '7binary@bk.ru'
		")->getResult();

		$i = 0;
		$total = count($users);

		foreach ($users as $user) {
			$emailService->send(
				$user->getUsername(),
				array('VidalMainBundle:Email:registration_confirm.html.twig', array('user' => $user)),
				'Благодарим за регистрацию на нашем портале!'
			);

			$output->writeln("... $i / $total");
			sleep(5);

			$i++;
		}

		$output->writeln('+++ vidal:user:confirmation completed!');
	}
}