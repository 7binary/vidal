<?php
namespace Vidal\MainBundle\Command;

use PHPExcel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExcelUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:excel_users')
            ->addArgument('numbers', InputArgument::IS_ARRAY, 'Number of year or month');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var PhpExcel $phpExcelObject */
        $phpExcelObject = $this->getContainer()->get('phpexcel')->createPHPExcelObject();
        $numbers = $input->getArgument('numbers');
        $number = empty($numbers) ? null : intval($numbers[0]);
        $users = $em->getRepository('VidalMainBundle:User')->forExcel($number);
        $usersSubs = $em->getRepository('VidalMainBundle:User')->forExcel($number, true);

        $phpExcelObject->getProperties()->setCreator('Vidal.ru')
            ->setLastModifiedBy('Vidal.ru')
            ->setTitle('Пользователи Видаля')
            ->setSubject('Пользователи Видаля');

        $phpExcelObject->getDefaultStyle()
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $phpExcelObject->setActiveSheetIndex(0);
        $worksheet = $phpExcelObject->getActiveSheet();

        $specialties = array();
        $regions = array();
        $cities = array();
        $countries = array();
        $profs = array();

        $sub_specialties = array();
        $sub_regions = array();
        $sub_cities = array();
        $sub_countries = array();
        $sub_profs = array();

        $specialties1 = array();
        $specialties2 = array();
        $specialties1sub = array();
        $specialties2sub = array();

        # ВСЕ ПОЛЬЗОВАТЕЛЯ
        for ($i = 0; $i < count($users); $i++) {
            # заполняем массив по специальности
            $key = $key1 = $users[$i]['specialty1'];
            $key2 = $users[$i]['specialty2'];
			$specialties[$key] = isset($specialties[$key]) ? $specialties[$key] + 1 : 1;
			$specialties[$key2] = isset($specialties[$key2]) ? $specialties[$key2] + 1 : 1;

            # заполняем массив по региону
            $keyRegion = $users[$i]['region'];
            if (!empty($keyRegion)) {
                $regions[$keyRegion] = isset($regions[$keyRegion]) ? $regions[$keyRegion] + 1 : 1;
            }

            $keyCity = $users[$i]['city'];
            if (!empty($keyCity)) {
                $cities[$keyCity] = isset($cities[$keyCity]) ? $cities[$keyCity] + 1 : 1;
            }

            $keyCountry = $users[$i]['country'];
            if (!empty($keyCountry)) {
                $countries[$keyCountry] = isset($countries[$keyCountry]) ? $countries[$keyCountry] + 1 : 1;
            }

            //extra
            if (isset($specialties1[$key1])) {
                $specialties1[$key1] = $specialties1[$key1] + 1;
            }
            else {
                $specialties1[$key1] = 0;
            }

            if (isset($specialties2[$key2])) {
                $specialties2[$key2] = $specialties2[$key2] + 1;
            }
            else {
                $specialties2[$key2] = 0;
            }

            if (isset($specialties2[$key1])) {
                $specialties2[$key1] = $specialties2[$key1] + 1;
            }
            else {
                $specialties2[$key1] = 0;
            }

            if ($users[$i]['digestSubscribed']) {
                if (isset($specialties1sub[$key1])) {
                    $specialties1sub[$key1] = $specialties1sub[$key1] + 1;
                }
                else {
                    $specialties1sub[$key1] = 0;
                }
                if (isset($specialties2sub[$key2])) {
                    $specialties2sub[$key2] = $specialties2sub[$key2] + 1;
                }
                else {
                    $specialties2sub[$key2] = 0;
                }
                if (isset($specialties2sub[$key1])) {
                    $specialties2sub[$key1] = $specialties2sub[$key1] + 1;
                }
                else {
                    $specialties2sub[$key1] = 0;
                }
            }
        }

        # ПОДПИСАННЫЕ ПОЛЬЗОВАТЕЛИ
        for ($i = 0; $i < count($usersSubs); $i++) {
            # заполняем массив по специальности
            $key = $key1 = $usersSubs[$i]['specialty1'];
            $key2 = $usersSubs[$i]['specialty2'];
            $sub_specialties[$key] = isset($sub_specialties[$key]) ? $sub_specialties[$key] + 1 : 1;
            $sub_specialties[$key2] = isset($sub_specialties[$key2]) ? $sub_specialties[$key2] + 1 : 1;

            # заполняем массив по региону
            $keyRegion = $usersSubs[$i]['region'];
            if (!empty($keyRegion)) {
                $sub_regions[$keyRegion] = isset($sub_regions[$keyRegion]) ? $sub_regions[$keyRegion] + 1 : 1;
            }

            $keyCity = $usersSubs[$i]['city'];
            if (!empty($keyCity)) {
                $sub_cities[$keyCity] = isset($sub_cities[$keyCity]) ? $sub_cities[$keyCity] + 1 : 1;
            }

            $keyCountry = $usersSubs[$i]['country'];
            if (!empty($keyCountry)) {
                $sub_countries[$keyCountry] = isset($sub_countries[$keyCountry]) ? $sub_countries[$keyCountry] + 1 : 1;
            }
        }

        foreach ($sub_specialties as $specialty => $items) {
            if (in_array($specialty, array('Клиническая фармакология', 'Провизор', 'Фармацевтика'))) {
                $key = 'специалисты в области фармации (Клиническая фармакология / Провизор / Фармацевтика)';
                $sub_profs[$key] = isset($sub_profs[$key]) ? $sub_profs[$key] + $items : $items;
            }
            elseif (in_array($specialty, array('Средний медицинский персонал', 'Фельдшерское дело'))) {
                $key = 'средний медицинский персонал (Средний медицинский персонал / Фельдшерское дело)';
                $sub_profs[$key] = isset($sub_profs[$key]) ? $sub_profs[$key] + $items : $items;
            }
            elseif (in_array($specialty, array('Администрация ЛПУ', 'Медико-социальная экспертиза', 'Организация здравоохранения и общественное здоровье', 'Фарм.индустрия'))) {
                $key = 'административный персонал (Администрация ЛПУ / Медико-социальная экспертиза / Организация здравоохранения и общественное здоровье / Фарм.индустрия)';
                $sub_profs[$key] = isset($sub_profs[$key]) ? $sub_profs[$key] + $items : $items;
            }
            elseif (in_array($specialty, array('Студент ВУЗа', 'Студент ССУЗа'))) {
                $key = 'студенты';
                $sub_profs[$key] = isset($sub_profs[$key]) ? $sub_profs[$key] + $items : $items;
            }
            elseif (in_array($specialty, array('Айти-специалист в медицине', 'Информационные технологии', 'Химия', 'Биология', 'Ветеринария'))) {
                $key = 'прочие (Айти-специалист в медицине / Информационные технологии / Химия / Биология / Ветеринария)';
                $sub_profs[$key] = isset($sub_profs[$key]) ? $sub_profs[$key] + $items : $items;
            }
            else {
                $key = 'врачи';
                $sub_profs[$key] = isset($sub_profs[$key]) ? $sub_profs[$key] + $items : $items;
            }
        }

		foreach ($specialties as $specialty => $items) {
			if (in_array($specialty, array('Клиническая фармакология', 'Провизор', 'Фармацевтика'))) {
				$key = 'специалисты в области фармации (Клиническая фармакология / Провизор / Фармацевтика)';
				$profs[$key] = isset($profs[$key]) ? $profs[$key] + $items : $items;
			}
			elseif (in_array($specialty, array('Средний медицинский персонал', 'Фельдшерское дело'))) {
				$key = 'средний медицинский персонал (Средний медицинский персонал / Фельдшерское дело)';
				$profs[$key] = isset($profs[$key]) ? $profs[$key] + $items : $items;
			}
			elseif (in_array($specialty, array('Администрация ЛПУ', 'Медико-социальная экспертиза', 'Организация здравоохранения и общественное здоровье', 'Фарм.индустрия'))) {
				$key = 'административный персонал (Администрация ЛПУ / Медико-социальная экспертиза / Организация здравоохранения и общественное здоровье / Фарм.индустрия)';
				$profs[$key] = isset($profs[$key]) ? $profs[$key] + $items : $items;
			}
			elseif (in_array($specialty, array('Студент ВУЗа', 'Студент ССУЗа'))) {
				$key = 'студенты';
				$profs[$key] = isset($profs[$key]) ? $profs[$key] + $items : $items;
			}
			elseif (in_array($specialty, array('Айти-специалист в медицине', 'Информационные технологии', 'Химия', 'Биология', 'Ветеринария'))) {
				$key = 'прочие (Айти-специалист в медицине / Информационные технологии / Химия / Биология / Ветеринария)';
				$profs[$key] = isset($profs[$key]) ? $profs[$key] + $items : $items;
			}
			else {
				$key = 'врачи';
				$profs[$key] = isset($profs[$key]) ? $profs[$key] + $items : $items;
			}
		}

        # заполняем первую страницу
        $this->firstColumn($worksheet, 'Все пользователи');
        $this->populateWorksheet($worksheet, $users);

        # заполняем вторую страницу
        $newsheetSubs = $phpExcelObject->createSheet(NULL, 1);
        $this->firstColumn($newsheetSubs, 'Подписанные на рассылку');
        $this->populateWorksheet($newsheetSubs, $usersSubs);

        # заполняем третью страницу со статистикой
        $newsheet = $phpExcelObject->createSheet(NULL, 2);
        $phpExcelObject->setActiveSheetIndex(1);

        arsort($specialties);
        arsort($regions);
        arsort($cities);
        arsort($profs);

        arsort($sub_specialties);
        arsort($sub_regions);
        arsort($sub_cities);
        arsort($sub_profs);

        $newsheet
            ->setTitle('Сводная статистика')
            ->setCellValue('A1', 'Профессия')
            ->setCellValue('B1', '')
            ->setCellValue('C1', 'Специальность')
            ->setCellValue('D1', '')
            ->setCellValue('E1', 'Регион')
            ->setCellValue('F1', '')
            ->setCellValue('G1', 'Город')
            ->setCellValue('H1', '')
            ->setCellValue('I1', 'Страна')
            ->setCellValue('J1', '');

        $alphabet = explode(' ', 'A B C D E F G H I J');

        foreach ($alphabet as $letter) {
            $newsheet->getColumnDimension($letter)->setWidth('30');
            $newsheet->getStyle($letter . '1')->applyFromArray(array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size' => 13,
                    'name' => 'Verdana',
                )
            ));
        }

        $i = 2;
        foreach ($profs as $prof => $qty) {
            $newsheet->setCellValue("A{$i}", $prof)->setCellValue("B{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($specialties as $specialty => $qty) {
            $newsheet->setCellValue("C{$i}", $specialty)->setCellValue("D{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($regions as $region => $qty) {
            $newsheet->setCellValue("E{$i}", $region)->setCellValue("F{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($cities as $city => $qty) {
            $newsheet->setCellValue("G{$i}", $city)->setCellValue("H{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($countries as $country => $qty) {
            $newsheet->setCellValue("I{$i}", $country)->setCellValue("J{$i}", $qty);
            $i++;
        }

        # заполняем четвертую страницу со статистикой
        $newsheet = $phpExcelObject->createSheet(NULL, 3);
        $phpExcelObject->setActiveSheetIndex(1);

        arsort($specialties1);
        arsort($specialties2);
        arsort($specialties1sub);
        arsort($specialties2sub);

        $newsheet
            ->setTitle('Сводная по специальностям')
            ->setCellValue('A1', 'Специальность основная')
            ->setCellValue('B1', '')
            ->setCellValue('C1', 'Специальность основная+доп')
            ->setCellValue('D1', '')
            ->setCellValue('E1', 'Специальность основная+доп только подписанные')
            ->setCellValue('F1', '');

        $alphabet = explode(' ', 'A B C D E F');

        foreach ($alphabet as $letter) {
            $newsheet->getColumnDimension($letter)->setWidth('30');
            $newsheet->getStyle($letter . '1')->applyFromArray(array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size' => 13,
                    'name' => 'Verdana',
                )
            ));
        }

        $i=2;
        foreach ($specialties1 as $specialty => $qty) {
            $newsheet->setCellValue("A{$i}", $specialty)->setCellValue("B{$i}", $qty);
            $i++;
        }

        $i=2;
        foreach ($specialties2 as $specialty => $qty) {
            $newsheet->setCellValue("C{$i}", $specialty)->setCellValue("D{$i}", $qty);
            $i++;
        }

        $i=2;
        foreach ($specialties2sub as $specialty => $qty) {
            $newsheet->setCellValue("E{$i}", $specialty)->setCellValue("F{$i}", $qty);
            $i++;
        }

        # пятая
        $newsheet = $phpExcelObject->createSheet(NULL, 4);
        $newsheet
            ->setTitle('Сводная статистика Подписанные')
            ->setCellValue('A1', 'Профессия')
            ->setCellValue('B1', '')
            ->setCellValue('C1', 'Специальность')
            ->setCellValue('D1', '')
            ->setCellValue('E1', 'Регион')
            ->setCellValue('F1', '')
            ->setCellValue('G1', 'Город')
            ->setCellValue('H1', '')
            ->setCellValue('I1', 'Страна')
            ->setCellValue('J1', '');

        $alphabet = explode(' ', 'A B C D E F G H I J');

        foreach ($alphabet as $letter) {
            $newsheet->getColumnDimension($letter)->setWidth('30');
            $newsheet->getStyle($letter . '1')->applyFromArray(array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size' => 13,
                    'name' => 'Verdana',
                )
            ));
        }

        $i = 2;
        foreach ($sub_profs as $prof => $qty) {
            $newsheet->setCellValue("A{$i}", $prof)->setCellValue("B{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($sub_specialties as $specialty => $qty) {
            $newsheet->setCellValue("C{$i}", $specialty)->setCellValue("D{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($sub_regions as $region => $qty) {
            $newsheet->setCellValue("E{$i}", $region)->setCellValue("F{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($sub_cities as $city => $qty) {
            $newsheet->setCellValue("G{$i}", $city)->setCellValue("H{$i}", $qty);
            $i++;
        }

        $i = 2;
        foreach ($sub_countries as $country => $qty) {
            $newsheet->setCellValue("I{$i}", $country)->setCellValue("J{$i}", $qty);
            $i++;
        }

        # Страница 6. Специальность - Регион - Город
        $newsheet = $phpExcelObject->createSheet();
        $page6 = array();

        for ($i = 0; $i < count($usersSubs); $i++) {
            $user = $usersSubs[$i];
            $sp1 = $user['specialty1'];
            $sp2 = $user['specialty2'];
            $region = $user['region'];
            $city = $user['city'];

            if (empty($region) || empty($city)) {
                continue;
            }

            if (!isset($page6[$sp1])) {
                $page6[$sp1] = array('regions' => array());
            }
            if (!isset($page6[$sp1]['regions'][$region])) {
                $page6[$sp1]['regions'][$region] = array('cities' => array(), 'total' => 0);
            }
            if (!isset($page6[$sp1]['regions'][$region]['cities'][$city])) {
                $page6[$sp1]['regions'][$region]['cities'][$city] = array('total' => 0);
            }
            $page6[$sp1]['regions'][$region]['total'] += 1;
            $page6[$sp1]['regions'][$region]['cities'][$city]['total'] += 1;

            if (!empty($sp2) && $sp1 != $sp2) {
                if (!isset($page6[$sp2])) {
                    $page6[$sp2] = array('regions' => array());
                }
                if (!isset($page6[$sp2]['regions'][$region])) {
                    $page6[$sp2]['regions'][$region] = array('cities' => array(), 'total' => 0);
                }
                if (!isset($page6[$sp2]['regions'][$region]['cities'][$city])) {
                    $page6[$sp2]['regions'][$region]['cities'][$city] = array('total' => 0);
                }
                $page6[$sp2]['regions'][$region]['total'] += 1;
                $page6[$sp2]['regions'][$region]['cities'][$city]['total'] += 1;
            }
        }

        $newsheet
            ->setTitle('Специальность-Регион-Город')
            ->setCellValue('A1', 'Специальность')
            ->setCellValue('B1', 'Регион')
            ->setCellValue('C1', 'Регион всего')
            ->setCellValue('D1', 'Город')
            ->setCellValue('E1', 'Город всего');

        $alphabet = explode(' ', 'A B C D E');

        foreach ($alphabet as $letter) {
            $newsheet->getColumnDimension($letter)->setWidth('30');
            $newsheet->getStyle($letter . '1')->applyFromArray(array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size' => 13,
                    'name' => 'Verdana',
                )
            ));
        }

        $i = 2;
        foreach ($page6 as $specialty => $specialtyData) {
            foreach ($specialtyData['regions'] as $region => $regionData) {
                foreach ($regionData['cities'] as $city => $cityData) {
                    $newsheet->setCellValue('A'.$i, $specialty);
                    $newsheet->setCellValue('B'.$i, $region);
                    $newsheet->setCellValue('C'.$i, $regionData['total']);
                    $newsheet->setCellValue('D'.$i, $city);
                    $newsheet->setCellValue('E'.$i, $cityData['total']);
                    $i++;
                }
            }
        }

        # Страница 7. РФ - Специальность - Регион - Город
        $newsheet = $phpExcelObject->createSheet();
        $page7 = array();

        for ($i = 0; $i < count($usersSubs); $i++) {
            $user = $usersSubs[$i];
            $sp1 = $user['specialty1'];
            $sp2 = $user['specialty2'];
            $region = $user['region'];
            $city = $user['city'];
            $country = $user['country'];

            if ($country != 'Россия') {
                continue;
            }

            if (empty($region) || empty($city)) {
                continue;
            }

            if (!isset($page7[$sp1])) {
                $page7[$sp1] = array('regions' => array());
            }
            if (!isset($page7[$sp1]['regions'][$region])) {
                $page7[$sp1]['regions'][$region] = array('cities' => array(), 'total' => 0);
            }
            if (!isset($page7[$sp1]['regions'][$region]['cities'][$city])) {
                $page7[$sp1]['regions'][$region]['cities'][$city] = array('total' => 0);
            }
            $page7[$sp1]['regions'][$region]['total'] += 1;
            $page7[$sp1]['regions'][$region]['cities'][$city]['total'] += 1;

            if (!empty($sp2) && $sp1 != $sp2) {
                if (!isset($page7[$sp2])) {
                    $page7[$sp2] = array('regions' => array());
                }
                if (!isset($page7[$sp2]['regions'][$region])) {
                    $page7[$sp2]['regions'][$region] = array('cities' => array(), 'total' => 0);
                }
                if (!isset($page7[$sp2]['regions'][$region]['cities'][$city])) {
                    $page7[$sp2]['regions'][$region]['cities'][$city] = array('total' => 0);
                }
                $page7[$sp2]['regions'][$region]['total'] += 1;
                $page7[$sp2]['regions'][$region]['cities'][$city]['total'] += 1;
            }
        }

        $newsheet
            ->setTitle('РФ-Специальность-Регион-Город')
            ->setCellValue('A1', 'Специальность')
            ->setCellValue('B1', 'Регион')
            ->setCellValue('C1', 'Регион всего')
            ->setCellValue('D1', 'Город')
            ->setCellValue('E1', 'Город всего');

        $alphabet = explode(' ', 'A B C D E');

        foreach ($alphabet as $letter) {
            $newsheet->getColumnDimension($letter)->setWidth('30');
            $newsheet->getStyle($letter . '1')->applyFromArray(array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size' => 13,
                    'name' => 'Verdana',
                )
            ));
        }

        $i = 2;
        foreach ($page7 as $specialty => $specialtyData) {
            foreach ($specialtyData['regions'] as $region => $regionData) {
                foreach ($regionData['cities'] as $city => $cityData) {
                    $newsheet->setCellValue('A'.$i, $specialty);
                    $newsheet->setCellValue('B'.$i, $region);
                    $newsheet->setCellValue('C'.$i, $regionData['total']);
                    $newsheet->setCellValue('D'.$i, $city);
                    $newsheet->setCellValue('E'.$i, $cityData['total']);
                    $i++;
                }
            }
        }

        ###################################################################################################
        $phpExcelObject->setActiveSheetIndex(0);

        $file = $this->getContainer()->getParameter('archive_dir') . DIRECTORY_SEPARATOR
            . ($number ? "users_{$number}.xlsx" : 'users.xlsx');

        $writer = $this->getContainer()->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $writer->save($file);

        $output->writeln('+++ vidal:excel_users completed!');
    }

    private function firstColumn($worksheet, $title)
    {
        $worksheet
            ->setTitle($title)
            ->setCellValue('A1', 'Специальность-1')
            ->setCellValue('B1', 'Специальность-2')
            ->setCellValue('C1', 'Город')
            ->setCellValue('D1', 'Регион')
            ->setCellValue('E1', 'Страна')
            ->setCellValue('F1', 'Зарегистр.')
            ->setCellValue('G1', 'ФИО');

        $alphabet = explode(' ', 'A B C D E F G');

        foreach ($alphabet as $letter) {
            $worksheet->getColumnDimension($letter)->setWidth('30');
            $worksheet->getStyle($letter . '1')->applyFromArray(array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'FF0000')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => 'FFFFFF'),
                    'size' => 13,
                    'name' => 'Verdana',
                )
            ));
        }
    }

    private function populateWorksheet($worksheet, $users)
    {
        for ($i = 0; $i < count($users); $i++) {
            $index = $i + 2;
            $name = $users[$i]['lastName'] . ' ' . $users[$i]['firstName'];
            if (!empty($users[$i]['surName'])) {
                $name .= ' ' . $users[$i]['surName'];
            }

            $worksheet
                ->setCellValue("A{$index}", $users[$i]['specialty1'])
                ->setCellValue("B{$index}", $users[$i]['specialty2'])
                ->setCellValue("C{$index}", $users[$i]['city'])
                ->setCellValue("D{$index}", $users[$i]['region'])
                ->setCellValue("E{$index}", $users[$i]['country'])
                ->setCellValue("F{$index}", $users[$i]['registered'])
                ->setCellValue("G{$index}", $name);
        }
    }
}