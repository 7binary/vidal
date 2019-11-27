<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExportController extends Controller
{
    /**
     * @Route("/export/atc-mkb" ,name="export_atc_mkb")
     * @Secure(roles="ROLE_SUPERADMIN")
     * @Template("VidalMainBundle:Export:atc_mkb.html.twig")
     */
    public function atcMkbAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $file = $this->container->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'export.json';

        if (file_exists($file)) {
            $json = file_get_contents($file);
            $lines = json_decode($json, true);
        }
        else {
            $lines = array();
            for ($i = 0; $i < 25; $i++) {
                $lines[] = array(
                    'i' => $i,
                    'name' => '',
                    'atc' => '',
                    'mkb' => '',
                    'atc_all' => '',
                    'mkb_all' => '',
                    'product' => array(),
                    'publication' => array(),
                    'art' => array(),
                    'article' => array(),
                );
            }
        }
        $origLines = $lines;

        # экспорт
        if ($request->isMethod('POST') && $request->request->get('export')) {
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
            $phpExcelObject->getProperties()->setCreator("Vidal.ru")
                ->setTitle("Vidal.ru - ссылки материалов по кодам АТХ и МКБ-10")
                ->setSubject("Vidal.ru - ссылки материалов по кодам АТХ и МКБ-10");

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Рубрика')
                ->setCellValue('B1', 'ATХ коды')
                ->setCellValue('C1', 'МКБ-10 коды')
                ->setCellValue('D1', 'Препараты')
                ->setCellValue('E1', 'Новости')
                ->setCellValue('G1', 'Статьи энциклопедии')
                ->setCellValue('F1', 'Статьи специалистам');

            $worksheet = $phpExcelObject->getActiveSheet();
            $letters = explode(' ', 'A B C D E F G');

            foreach ($letters as $letter) {
                $worksheet->getColumnDimension($letter)->setAutoSize('true');
                $worksheet->getStyle($letter . '1')->getFont()->getColor()->setRGB('FF0000');
            }

            for ($i = 0; $i < count($lines); $i++) {
                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue('A' . ($i + 2), $lines[$i]['name'])
                    ->setCellValue('B' . ($i + 2), $lines[$i]['atc'])
                    ->setCellValue('C' . ($i + 2), $lines[$i]['mkb'])
                    ->setCellValue('D' . ($i + 2), implode("\n", $lines[$i]['product']))
                    ->setCellValue('E' . ($i + 2), implode("\n", $lines[$i]['publication']))
                    ->setCellValue('F' . ($i + 2), implode("\n", $lines[$i]['article']))
                    ->setCellValue('G' . ($i + 2), implode("\n", $lines[$i]['art']));

                $phpExcelObject->getActiveSheet()->getStyle('D' . ($i + 2))->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getStyle('E' . ($i + 2))->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getStyle('F' . ($i + 2))->getAlignment()->setWrapText(true);
                $phpExcelObject->getActiveSheet()->getStyle('G' . ($i + 2))->getAlignment()->setWrapText(true);
            }

            $phpExcelObject->getActiveSheet()->setTitle('Simple');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);

            // create the writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            // create the response
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // adding headers
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment;filename=Ссылки_материалов_Видаля_по_кодам.xls');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');

            return $response;
        }

        # сохранение
        if ($request->isMethod('POST')) {
            $lines = $request->request->get('line');
            for ($i =0; $i < count($lines); $i++) {
                $lines[$i]['product'] = $origLines[$i]['product'];
            }

            foreach ($lines as &$line) {
                # АТХ
                $atcCodes = $line['atc'];

                if (!empty($atcCodes)) {
                    $children = array();
                    foreach (explode(',', $atcCodes) as $atcCode) {
                        $atcCode = trim($atcCode);
                        $children = array_merge($children, $em->getRepository('VidalDrugBundle:ATC')->findChildren($atcCode));
                        $children[] = $atcCode;
                    }

                    $children = array_unique($children);

                    # исключаем лишние со знаком -
                    foreach ($atcCodes as $atcCode) {
                        if (substr($atcCode, 0, 1) == '-') {
                            if (($key = array_search($atcCode, $children)) !== false) {
                                unset($children[$key]);
                            }
                            $atcCode = substr($atcCode, 1);
                            if (($key = array_search($atcCode, $children)) !== false) {
                                unset($children[$key]);
                            }
                        }
                    }
                    $atcCodesAll = $children;
                    $line['atc_all'] = implode(' ', $atcCodesAll);

                    # находим препараты
                    if ($request->request->get('product')) {
                        $productsByAtc = $em->getRepository("VidalDrugBundle:Product")->findAllByATCCode($atcCodesAll);
                        if (!empty($productsByAtc)) {
                            $links = array();
                            foreach ($productsByAtc as $p) {
                                $links[] = "https://www.vidal.ru/drugs/{$p['uri']}";
                            }
                            $line['product'] = $links;
                        }
                        else {
                            $line['product'] = array();
                        }
                    }

                    # находим новости
                    $publicationsByAtc = $em->getRepository("VidalDrugBundle:Publication")->findByAtc($atcCodesAll);
                    if (!empty($publicationsByAtc)) {
                        $links = array();
                        foreach ($publicationsByAtc as $p) {
                            $links[] = "https://www.vidal.ru/novosti/{$p['link']}-{$p['id']}";
                        }
                        $line['publication'] = $links;
                    }
                    else {
                        $line['publication'] = array();
                    }

                    # находим статьи энц
                    $articlesByAtc = $em->getRepository("VidalDrugBundle:Article")->articlesByAtc($atcCodesAll);
                    if (!empty($articlesByAtc)) {
                        $links = array();
                        foreach ($articlesByAtc as $a) {
                            $links[] = "https://www.vidal.ru/encyclopedia/{$a['rubrique']}/{$a['link']}";
                        }
                        $line['article'] = $links;
                    }
                    else {
                        $line['article'] = array();
                    }

                    # находим статьи спец
                    $artByAtc = $em->getRepository("VidalDrugBundle:Art")->artsByAtc($atcCodesAll);
                    if (!empty($artByAtc)) {
                        $links = array();
                        foreach ($artByAtc as $a) {
                            // "{{ path('art', {'url':art.rubriqueUrl ~ '/' ~ (art.typeUrl ? art.typeUrl ~ '/') ~ (art.categoryUrl ? art.categoryUrl ~ '/') ~ art.link ~ '~' ~ art.id }) }}">{{ art.title|raw }}</a>
                            $url = 'https://www.vidal.ru/vracham/';
                            if (!empty($a['rubriqueUrl'])) {
                                $url .= $a['rubriqueUrl'] . '/';
                            }
                            if (!empty($a['typeUrl'])) {
                                $url .= $a['typeUrl'] . '/';
                            }
                            if (!empty($a['categoryUrl'])) {
                                $url .= $a['categoryUrl'] . '/';
                            }
                            $url .= $a['link'];
                            $links[] = $url;
                        }
                        $line['art'] = $links;
                    }
                    else {
                        $line['art'] = array();
                    }
                }

                # МКБ-10
                $mkbCodes = $line['mkb'];
                if (!empty($mkbCodes)) {
                    $children = array();
                    foreach (explode(',', $mkbCodes) as $mkbCode) {
                        $mkbCode = trim($mkbCode);
                        $children = array_merge($children, $em->getRepository('VidalDrugBundle:Nozology')->findChildren($mkbCode));
                    }
                    $children = array_unique($children);

                    # исключаем лишние со знаком -
                    foreach ($mkbCodes as $mkbCode) {
                        if (substr($mkbCode, 0, 1) == '-') {
                            if (($key = array_search($mkbCode, $children)) !== false) {
                                unset($children[$key]);
                            }
                            $mkbCode = substr($mkbCode, 1);
                            if (($key = array_search($mkbCode, $children)) !== false) {
                                unset($children[$key]);
                            }
                        }
                    }
                    $mkbCodesAll = $children;
                    $line['mkb_all'] = implode(' ', $mkbCodesAll);

                    # находим статьи энц
                    $articlesByNosology = $em->getRepository("VidalDrugBundle:Article")->articlesByNosology($mkbCodesAll);
                    if (!empty($articlesByNosology)) {
                        $links = empty($line['article']) ? array() : $line['article'];
                        foreach ($articlesByNosology as $a) {
                            $links[] = "https://www.vidal.ru/encyclopedia/{$a['rubrique']}/{$a['link']}";
                        }
                        $line['article'] = array_unique($links);
                    }

                    # находим новости
                    $publicationsByMkb = $em->getRepository("VidalDrugBundle:Publication")->findByMkb($mkbCodesAll);
                    if (!empty($publicationsByMkb)) {
                        $links = empty($line['publication']) ? array() : $line['publication'];
                        foreach ($publicationsByMkb as $p) {
                            $links[] = "https://www.vidal.ru/novosti/{$p['link']}-{$p['id']}";
                        }
                        $line['publication'] = array_unique($links);
                    }

                    # находим статьи спец
                    $artByMkb = $em->getRepository("VidalDrugBundle:Art")->artsByMkb($mkbCodesAll);
                    if (!empty($artByMkb)) {
                        $links = empty($line['art']) ? array() : $line['art'];
                        foreach ($artByMkb as $a) {
                            $url = 'https://www.vidal.ru/vracham/';
                            if (!empty($a['rubriqueUrl'])) {
                                $url .= $a['rubriqueUrl'] . '/';
                            }
                            if (!empty($a['typeUrl'])) {
                                $url .= $a['typeUrl'] . '/';
                            }
                            if (!empty($a['categoryUrl'])) {
                                $url .= $a['categoryUrl'] . '/';
                            }
                            $url .= $a['link'];
                            $links[] = $url;
                        }
                        $line['art'] = array_unique($links);
                    }

                    # находим препараты
                    if ($request->request->get('product')) {
                        $productsByMkb = $em->getRepository("VidalDrugBundle:Product")->findByNosologies($mkbCodesAll);
                        if (!empty($productsByMkb)) {
                            $links = empty($line['product']) ? array() : $line['product'];
                            foreach ($productsByMkb as $p) {
                                $links[] = empty($p['url'])
                                    ? "https://www.vidal.ru/drugs/{$p['Name']}__{$p['ProductID']}"
                                    : "https://www.vidal.ru/drugs/{$p['url']}";
                            }
                            $line['product'] = $links;
                        }
                    }
                }
            }

            $lines = json_encode($lines, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            file_put_contents($file, $lines);

            return $this->redirect($this->generateUrl('export_atc_mkb'));
        }

        return array(
            'title' => 'Экспорт материалов АТХ-МКБ',
            'noYad' => true,
            'menu_left' => 'export_atc_mkb',
            'lines' => $lines,
        );
    }
}