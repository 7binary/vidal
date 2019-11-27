<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

# Команда отправки подтверждения регистрации
class RegistrationCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:registration_resend')
			->setDescription('Send registration to users')
			->addOption('all', null, InputOption::VALUE_NONE, 'Send digest to every subscribed user')
			->addOption('me', null, InputOption::VALUE_NONE, 'Send digest to 7binary@gmail.com')
			->addOption('local', null, InputOption::VALUE_NONE, 'Send digest from 7binary@list.ru');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		# снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
		set_time_limit(0);
		ini_set('memory_limit', -1);

		# опции не указаны - выводим мануал
		if (!$input->getOption('all') && !$input->getOption('me') && !$input->getOption('local')) {
			$output->writeln('=> Error: uncorrect syntax. READ BELOW');
			$output->writeln('$ php app/console evrika:digest --test');
			$output->writeln('$ php app/console evrika:digest --all');
			$output->writeln('$ php app/console evrika:digest --me');
			$output->writeln('$ php app/console evrika:digest --me --local');
			return false;
		}

		$container  = $this->getContainer();
		/** @var EntityManager $em */
		$em         = $container->get('doctrine')->getManager();
		$templating = $container->get('templating');
		$subject    = 'Пожалуйста, подтвердите регистрацию на портале Vidal.ru';
		$qb         = $em->createQueryBuilder();

		$qb->select("u.username, u.id, DATE_FORMAT(u.created, '%Y-%m-%d_%H:%i:%s') as created,
					u.firstName, u.surName, u.password, u.hash")
			->from('VidalMainBundle:User', 'u')
			->where('u.enabled = TRUE')
			->andWhere('u.digestSubscribed = TRUE');

		# рассылка всем подписанным врачам
		if ($input->getOption('all')) {
			$output->writeln("Sending: in progress to ALL subscribed users...");

			$qb->andWhere('u.emailConfirmed = FALSE');

			$orX = $qb->expr()->orX();
			foreach ($this->getDates() as $date) {
				$orX->add("u.created LIKE '$date%'");
			}
			$qb->andWhere($orX);

			$users = $qb->getQuery()->getResult();

			foreach ($users as $user) {
				$template = $templating->render('VidalMainBundle:Digest:registration.html.twig', array('user' => $user));
				$this->send($user['username'], $user['firstName'], $template, $subject);
			}
		}

		# отправить самому себе
		if ($input->getOption('me')) {
			$email = '7binary@gmail.com';
			$output->writeln("Sending: in progress to $email");

			$qb->where('u.username = :email')->setParameter('email', $email);

			$user = $qb->getQuery()->getSingleResult();

			$template = $templating->render('VidalMainBundle:Digest:registration.html.twig', array('user' => $user));
			$local    = $input->getOption('local');

			$this->send($user['username'], $user['firstName'], $template, $subject, $local);
		}

		return true;
	}

	private function send($email, $to, $body, $subject, $local = false)
	{
		$mail = new PHPMailer();

		$mail->isSMTP();
		$mail->isHTML(true);
		$mail->CharSet  = 'UTF-8';
		$mail->From     = 'maillist@vidal.ru';
		$mail->FromName = 'Портал «Vidal.ru»';
		$mail->Subject  = $subject;
		$mail->Host     = '127.0.0.1';
		$mail->Body     = $body;
		$mail->addAddress($email, $to);
		$mail->addCustomHeader('Precedence', 'bulk');

		if ($local) {
			$mail->Host       = 'smtp.gmail.com';
			$mail->From       = 'binacy@gmail.com';
			$mail->SMTPSecure = 'ssl';
			$mail->Port       = 465;
			$mail->SMTPAuth   = true;
			$mail->Username   = 'binacy@gmail.com';
			$mail->Password   = '2q32q3q2';
		}
		else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
            $mail->Port = 25;
        }

		$result = $mail->send();
		$mail   = null;

		return $result;
	}

	private function getDates()
	{
		$dates = array();
		$date  = new \DateTime('now');

		for ($i = 1; $i <= 3; $i++) {
			$date->modify('-1 month');
			$dates[] = $date->format('Y-m-d');
		}

		return $dates;
	}
}
