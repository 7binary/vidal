<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProductsSubmainCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:products_submain');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:products_submain started');

        /** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager('drug');

        $title = "Выгрузка склеенных препаратов";

        $phpExcelObject = $this->getContainer()->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator('')->setTitle($title)->setSubject('');

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Название')
            ->setCellValue('B1', 'ID препарата')
            ->setCellValue('C1', 'URL')
            ->setCellValue('D1', 'ID родителя');

        $worksheet = $phpExcelObject->getActiveSheet();
        $letters = explode(' ', 'A B C D');

        foreach ($letters as $letter) {
            $worksheet->getColumnDimension($letter)->setAutoSize('true');
            $worksheet->getStyle($letter . '1')->getFont()->getColor()->setRGB('FF0000');
        }

        $products = $em->getRepository('VidalDrugBundle:Product')->findForExport();

        $productsGrouped = array();
        foreach ($products as $product) {
            $key = $product['ProductID'];
            $productsGrouped[$key] = $product;
        }

        if (!empty($products)) {
            for ($i = 0; $i < count($products); $i++) {
                $p = $products[$i];
                $parentId = empty($p['ParentID']) ? (empty($p['MainID']) ? null : $p['MainID']) : $p['ParentID'];

                if ($parentId) {
                    $parent = $productsGrouped[$parentId];
                    $url = empty($parent['url'])
                        ? "https://www.vidal.ru/drugs/{$parent['Name']}__{$parent['ProductID']}"
                        : "https://www.vidal.ru/drugs/{$parent['url']}";
                }
                else {
                    $url = empty($p['url'])
                        ? "https://www.vidal.ru/drugs/{$p['Name']}__{$p['ProductID']}"
                        : "https://www.vidal.ru/drugs/{$p['url']}";
                }

                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A' . ($i + 2), $p['RusName2'])
                    ->setCellValue('B' . ($i + 2), $p['ProductID'])
                    ->setCellValue('C' . ($i + 2), $url)
                    ->setCellValue('D' . ($i + 2), $parentId);
            }
        }

        $phpExcelObject->getActiveSheet()->setTitle($title);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        $filename = 'products_merged.xlsx';
        $file = $this->getContainer()->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $filename;

        $writer = $this->getContainer()->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $writer->save($file);

        $output->writeln("+++ vidal:products_submain completed!");
	}
}