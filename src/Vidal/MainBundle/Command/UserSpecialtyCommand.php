<?php
namespace Vidal\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserSpecialtyCommand extends ContainerAwareCommand
{
	protected $specialties = array(
		'студент медицинского вуза'    => 88,
		'терапевт'                     => 31,
		'кардиолог'                    => 11,
		'иммунолог-аллерголог'         => 2,
		'фармацевт/провизор'           => 86,
		'фармацевт'                    => 86,
		'провизор'                     => 86,
		'анестезиолог-реаниматолог'    => 3,
		'клинический фармаколог'       => 66,
		'стоматолог'                   => 29,
		'венеролог'                    => 8,
		'дерматолог'                   => 8,
		'эндоскопист'                  => 39,
		'хирург'                       => 37,
		'акушер-гинеколог'             => 1,
		'фтизиатр'                     => 95,
		'гастроэнтеролог'              => 4,
		'инфекционист'                 => 10,
		'гематолог'                    => 5,
		'гепатолог'                    => 63,
		'айти-специалист в медицине'   => 96,
		'врач-лаборант'                => 12,
		'нефролог'                     => 62,
		'невропатолог'                 => 16,
		'педиатр'                      => 22,
		'неонатолог'                   => 14,
		'онколог'                      => 17,
		'офтальмолог'                  => 20,
		'ортопед'                      => 32,
		'пульмонолог'                  => 25,
		'рентгенолог'                  => 27,
		'ревматолог'                   => 26,
		'уролог'                       => 33,
		'средний медицинский персонал' => 97,
		'ангиолог'                     => 98,
		'андролог'                     => 99,
		'фарм.индустрия'               => 100,
		'администрация ЛПУ'            => 101,
		'ветеринар'                    => 102,
		'спортивный врач'              => 81,
		'реабилитолог'                 => 103,
		'эндокринолог'                 => 38,
		'косметолог'                   => 68,
		'психиатр'                     => 24,
		'проктолог'                    => 104,
		'ЛОР-врач'                     => 19,
		'нарколог'                     => 15,
		'паталогоанатом'               => 21,
		'сексолог'                     => 105,
		'трансплантолог'               => 64,
		'эпидемиолог'                  => 40,
		'судебно-медицинский эксперт'  => 30,
	);

	protected function configure()
	{
		$this->setName('vidal:user_specialty');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:user_specialty started');

		$container   = $this->getContainer();
		$em          = $container->get('doctrine')->getManager();
		$pdo         = $em->getConnection();
		$filename    = $this->getContainer()->get('kernel')->getRootDir() . '/User.orig.xlsx';
		$objPHPExcel = \PHPExcel_IOFactory::load($filename);

		//  Get worksheet dimensions
		$sheet         = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();

		$stmt = $pdo->prepare('UPDATE user SET primarySpecialty_id = ?, secondarySpecialty_id = ? WHERE username = ?');
		$i    = 0;

		$output->writeln('Total rows: ' . $highestRow);

		for ($row = 1; $row <= $highestRow; $row++) {
			$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

			if (isset($rowData[0])
				&& isset($rowData[0][6])
				&& isset($rowData[0][8])
				&& !empty($rowData[0][1])
				&& !empty($rowData[0][6])
			) {
				$email     = $rowData[0][1];
				$primary   = $this->getSpecialty($rowData[0][6]);
				$secondary = $this->getSpecialty($rowData[0][8]);

				$stmt->bindParam(1, $primary);
				$stmt->bindParam(2, $secondary);
				$stmt->bindParam(3, $email);

				$stmt->execute();
				$i++;

				if ($i && $i % 100 == 0) {
					$output->writeln('... ' . $i);
				}
			}
		}

		$output->writeln("+++ vidal:user_specialty $i loaded!");
	}

	private function getSpecialty($key)
	{
		if (!isset($this->specialties[$key])) {
			return null;
		}

		return $this->specialties[$key];
	}
}