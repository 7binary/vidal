<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации названий для автодополнения поисковой строки
 *
 * @package Vidal\DrugBundle\Command
 */
class DocumentImageCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:document_image')
			->setDescription('Changes to documents content to add images');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:document_image started');

		$em   = $this->getContainer()->get('doctrine')->getManager('drug');
		$repo = $em->getRepository('VidalDrugBundle:Document');

		# Картинка berotek jpg вставляется в рубрику Режим дозирования по месту где написано рис 1 в препараты Беродуал Н и Атровент Н
		$documentIds = array(30293, 30299);
		foreach ($documentIds as $id) {
			if ($document = $repo->findById($id)) {
				$dosage = $document->getDosage();
				$dosage = preg_replace('/Рис 1./ui', '<img src="/upload/documents/Berotek.jpg"/>', $dosage);
				$document->setDosage($dosage);
				$em->flush($document);
			}
		}

		# Препарат Диспорт в разделе режим Дозирования имеет 2 картинки: 111copy.jpg – это рис1; 222copy.jpg – это рис.2
		if ($document = $repo->findById(31080)) {
			$dosage   = $document->getDosage();
			$patterns = array('/РИС 1./ui', '/РИС 2./ui');
			$replace  = array(
				'<img src="/upload/documents/Differelin/111 copy.jpg"/>',
				'<img src="/upload/documents/Differelin/222 copy.jpg"/>'
			);
			$dosage   = preg_replace($patterns, $replace, $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}

		# Препарат Мальтофер для инъекций имеет в режиме дозирования 4 картинки. Порядковые номера файлов соответствуют месту расположения внутри текста.
		if ($document = $repo->findById(35170)) {
			$dosage   = $document->getDosage();
			$patterns = array('/РИС.1/u', '/РИС.2/u', '/РИС. 3/u', '/РИС.4/u');
			$replace  = array(
				'<img src="/upload/documents/maltofer/Maltofer IM-1.jpg"/>',
				'<img src="/upload/documents/maltofer/Maltofer IM-2.jpg"/>',
				'<img src="/upload/documents/maltofer/Maltofer IM-3.jpg"/>',
				'<img src="/upload/documents/maltofer/Maltofer IM-4.jpg"/>',
			);
			$dosage   = preg_replace($patterns, $replace, $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}

		# Препарат Онбрез Бризхалер имеет в режиме дозирования 10 картинок. Номера файлов соответствуют месту расположения картинки внутри текста.
		if ($document = $repo->findById(31465)) {
			$dosage   = $document->getDosage();
			$patterns = array(
				'/РИС. 1/ui',
				'/РИС. 2/ui',
				'/РИС. 3/ui',
				'/РИС. 4/ui',
				'/РИС. 5/ui',
				'/РИС. 6/ui',
				'/РИС.7/ui',
				'/РИС.8/ui',
				'/РИС.9/ui',
				'/РИС.10/ui',
				'/РИС.11/ui',
			);
			$replace  = array(
				'<img src="/upload/documents/ONBREZ/onbrez-1.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-2.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-3.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-4.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-5.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-6.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-7.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-8.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-9.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-10.jpg"/>',
				'<img src="/upload/documents/ONBREZ/onbrez-11.jpg"/>',
			);
			$dosage   = preg_replace($patterns, $replace, $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}

		# Препарат Симатулин Аутожель имеет внутри текста 7 картинок. Номера файлов соответствуют месту расположения картинки внутри текста.
		if ($document = $repo->findById(31046)) {
			$dosage   = $document->getDosage();
			$patterns = array(
				'/РИС.1/ui',
				'/РИС.2/ui',
				'/РИС 3./ui',
				'/РИС. 4/ui',
				'/РИС 5/ui',
				'/РИС 6./ui',
				'/РИС 7./ui',
			);
			$replace  = array(
				'<img src="/upload/documents/Somatullin/1.jpg"/>',
				'<img src="/upload/documents/Somatullin/2.jpg"/>',
				'<img src="/upload/documents/Somatullin/3.jpg"/>',
				'<img src="/upload/documents/Somatullin/4.jpg"/>',
				'<img src="/upload/documents/Somatullin/5.jpg"/>',
				'<img src="/upload/documents/Somatullin/6.jpg"/>',
				'<img src="/upload/documents/Somatullin/7.jpg"/>',
			);
			$dosage   = preg_replace($patterns, $replace, $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}

		# Бревиблок в режиме дозирования рис 1 – картинка brevibloc-pic.jpg
		if ($document = $repo->findById(36515)) {
			$dosage = $document->getDosage();
			$dosage = preg_replace('/РИС.1/ui', '<img src="/upload/documents/Brevibloc_pict.jpg"/>', $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}

		# Cимпони  в режиме дозирования рис.1 – картинка simponi-pict1, рис.2 – simponi_pict.2
		if ($document = $repo->findById(31845)) {
			$dosage   = $document->getDosage();
			$patterns = array('/РИС 1/ui', '/Рис. 2/ui');
			$replace  = array(
				'<img src="/upload/documents/Simponi_pict1.jpg"/>',
				'<img src="/upload/documents/Simponi_pict2.jpg"/>',
			);
			$dosage   = preg_replace($patterns, $replace, $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}

		# Каверджект В режим дозирования Перед словами Диаграмма А caverject1 Перед словами Диаграмма Б caverject2 И вместо слов Рис.1: Введение иглы в место проведения инъекции Caverject3
		if ($document = $repo->findById(1215)) {
			$dosage   = $document->getDosage();
			$patterns = array(
				'/Диаграмма А/ui',
				'/Диаграмма Б/ui',
				'/Рис.1: Введение иглы в место проведения инъекции/ui',
			);
			$replace  = array(
				'<img src="/upload/documents/Caverject/сaverject-1.jpg"/>',
				'<img src="/upload/documents/Caverject/сaverject-2.jpg"/>',
				'<img src="/upload/documents/Caverject/сaverject-3.jpg"/>',
			);
			$dosage   = preg_replace($patterns, $replace, $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}

		# Гемате П ID 32104 в режиме дозирования проставлены  Рис.1 – Рис.9 после этих слов должны идти по порядку файлы из подкаталога Гемате с соответствующей нумерацией.
		if ($document = $repo->findById(32104)) {
			$dosage   = $document->getDosage();
			$patterns = array(
				'/РИС.1/ui',
				'/РИС. 2/ui',
				'/РИС. 3/ui',
				'/РИС.4/ui',
				'/РИС. 5/ui',
				'/РИС. 6/ui',
				'/РИС. 7/ui',
				'/РИС. 8/ui',
				'/РИС. 9/ui',
			);
			$replace  = array(
				'<img src="/upload/documents/Гемате/pict1.jpg"/>',
				'<img src="/upload/documents/Гемате/pict2.jpg"/>',
				'<img src="/upload/documents/Гемате/pict3.jpg"/>',
				'<img src="/upload/documents/Гемате/pict4.jpg"/>',
				'<img src="/upload/documents/Гемате/pict5.jpg"/>',
				'<img src="/upload/documents/Гемате/pict6.jpg"/>',
				'<img src="/upload/documents/Гемате/pict7.jpg"/>',
				'<img src="/upload/documents/Гемате/pict8.jpg"/>',
				'<img src="/upload/documents/Гемате/pict9.jpg"/>',
			);
			$dosage   = preg_replace($patterns, $replace, $dosage);
			$document->setDosage($dosage);
			$em->flush($document);
		}


		$output->writeln('+++ vidal:document_image completed!');
	}
}