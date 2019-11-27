<?php
namespace Vidal\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExcelSearchCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:excel_search');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- vidal:excel_search started...');

		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);

		$em             = $this->getContainer()->get('doctrine')->getManager();
		$phpExcelObject = $this->getContainer()->get('phpexcel')->createPHPExcelObject();
		$searches       = $em->getRepository('VidalMainBundle:Search')->forExcel();
		$title = 'Поисковые запросы Видаля';

		$phpExcelObject->getProperties()->setCreator('Vidal.ru')
			->setLastModifiedBy('Vidal.ru')
			->setTitle($title)
			->setSubject($title);

		$phpExcelObject->getDefaultStyle()
			->getAlignment()
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

		$phpExcelObject->setActiveSheetIndex(0);
		$worksheet = $phpExcelObject->getActiveSheet();

		$worksheet
			->setTitle($title)
			->setCellValue('A1', 'Фраза поиска')
			->setCellValue('B1', 'Время поиска')
			->setCellValue('C1', 'Дата поиска')
			->setCellValue('D1', 'Предыдущая страница')
			->setCellValue('E1', 'Без результатов')
			->setCellValue('F1', 'Слишком короткий');

		$alphabet = explode(' ', 'A B C D E F');

		foreach ($alphabet as $letter) {
			$worksheet->getColumnDimension($letter)->setWidth('30');
			$worksheet->getStyle($letter . '1')->applyFromArray(array(
				'fill' => array(
					'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'FF0000')
				),
				'font' => array(
					'bold'  => true,
					'color' => array('rgb' => 'FFFFFF'),
					'size'  => 13,
					'name'  => 'Verdana',
				)
			));
		}

		for ($i = 0; $i < count($searches); $i++) {
			$index = $i + 2;

			$worksheet
				->setCellValue("A{$index}", $searches[$i]['query'])
				->setCellValue("B{$index}", $searches[$i]['created']->format('H:i'))
				->setCellValue("C{$index}", $searches[$i]['created']->format('d.m.Y'))
				->setCellValue("D{$index}", $searches[$i]['referer'])
				->setCellValue("E{$index}", $searches[$i]['withoutResults'] == true ? 'да' : '' )
				->setCellValue("F{$index}", $searches[$i]['tooShort'] == true ? 'да' : '');
		}

		###################################################################################################
		$phpExcelObject->setActiveSheetIndex(0);

		$file = $this->getContainer()->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'search.xlsx';

		$writer = $this->getContainer()->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
		$writer->save($file);

		$output->writeln('+++ vidal:excel_search completed!');
	}
}