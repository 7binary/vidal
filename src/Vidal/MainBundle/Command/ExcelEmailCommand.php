<?php
namespace Vidal\MainBundle\Command;

use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExcelEmailCommand extends ContainerAwareCommand
{
	protected $emails = array('m.yudintseva@vidal.ru');

	protected function configure()
	{
		$this->setName('vidal:excel_email');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		foreach ($this->emails as $email) {
			$this->send($email);
			$output->writeln("+++ send email to <$email>");
		}
	}

	private function send($email)
	{
		$mail = new PHPMailer();

		$mail->isSMTP();
		$mail->isHTML(true);
		$mail->CharSet  = 'UTF-8';
		$mail->FromName = 'Портал Vidal.ru';
		$mail->Subject  = 'Отчет по пользователям Vidal';
		$mail->Body     = '<h2>Отчет содержится в прикрепленных файлах</h2>';
		$mail->addAddress($email);

		$mail->Host = '127.0.0.1';
		$mail->From = 'maillist@vidal.ru';
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
        $mail->Port = 25;

		$file = $this->getContainer()->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..'
			. DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR
			. 'users.xlsx';

		$mail->AddAttachment($file, 'Отчет Vidal: по всем пользователям.xlsx');

		$prevMonth = new \DateTime('now');
		$prevMonth = $prevMonth->modify('-1 month');
		$prevMonth = intval($prevMonth->format('m'));

		$file = $this->getContainer()->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..'
			. DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR
			. "users_{$prevMonth}.xlsx";
		$name = 'Отчет Vidal: за прошедший месяц - ' . $this->getMonthName($prevMonth) . '.xlsx';

		$mail->AddAttachment($file, $name);

		$mail->send();
	}

	public function getMonthName($month)
	{
		switch ($month) {
			case 1:
				return 'Январь';
			case 2:
				return 'Февраль';
			case 3:
				return 'Март';
			case 4:
				return 'Апрель';
			case 5:
				return 'Май';
			case 6:
				return 'Июнь';
			case 7:
				return 'Июль';
			case 8:
				return 'Август';
			case 9:
				return 'Сентябрь';
			case 10:
				return 'Октябрь';
			case 11:
				return 'Ноябрь';
			case 12:
				return 'Декабрь';
			default:
				return '';
		}
	}
}