<?php
namespace Vidal\MainBundle\Command;

use PHPExcel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExcelProductsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:excel_documents');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        $container = $this->getContainer();
        $drugEm  = $container->get('doctrine')->getManager('drug');
        $drugVeterenar  = $container->get('doctrine')->getManager('veterinar');

        $routing = $container->get('router');

        $drugProducts = $drugEm->createQuery("
            SELECT product.RusName, product.uri
            FROM VidalDrugBundle:Product product
        ")->getResult();


        $veterenarProducts = $drugVeterenar->createQuery("
            SELECT product.RusName, product.Name, product.ProductID
            FROM VidalVeterinarBundle:Product product
        ")->getResult();

        /** @var PhpExcel $phpExcelObject */
        $phpExcelObject = $this->getContainer()->get('phpexcel')->createPHPExcelObject();


        $phpExcelObject->getProperties()->setCreator('Vidal.ru')
            ->setLastModifiedBy('Vidal.ru')
            ->setTitle('Препараты Видаля')
            ->setSubject('Препараты Видаля');

        $phpExcelObject->getDefaultStyle()
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $phpExcelObject->setActiveSheetIndex(0);
        $drugProductsSheet = $phpExcelObject->getActiveSheet();

        $drugProductsSheet
            ->setTitle('Основаня база')
            ->setCellValue('A1', 'Название препарата')
            ->setCellValue('B1', 'Ссылка на страницу');

        $i = 2;
        foreach ($drugProducts as $product)
        {
            if($product['uri']) {
                $url = $routing->generate('product_url', ['EngName' => $product['uri']]);
                $url="https://vidal.ru".$url;
                $drugProductsSheet->setCellValue("A{$i}", $product['RusName'])->setCellValue("B{$i}", $url);
                $i++;
            }
        }

        $veterenarProductsSheet = $phpExcelObject->createSheet(NULL, 2);
        $phpExcelObject->setActiveSheetIndex(1);

        $veterenarProductsSheet
            ->setTitle('База ветеринарии')
            ->setCellValue('A1', 'Название препарата')
            ->setCellValue('B1', 'Ссылка на страницу');

        $i = 2;
        foreach ($veterenarProducts as $product)
        {
            $url = $routing->generate('v_product', [
                'EngName' => $product['Name'],
                'ProductID' => $product['ProductID']
            ]);

            $url="https://vidal.ru".$url;
            $veterenarProductsSheet->setCellValue("A{$i}", $product['RusName'])->setCellValue("B{$i}", $url);
            $i++;
        }

        $phpExcelObject->setActiveSheetIndex(0);

        $file = $this->getContainer()->getParameter('archive_dir') . DIRECTORY_SEPARATOR . "products_.xlsx";

        $writer = $this->getContainer()->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $writer->save($file);

        $output->writeln('+++ vidal:excel_documents completed!');
    }
}