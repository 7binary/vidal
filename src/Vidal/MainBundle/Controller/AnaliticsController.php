<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Vidal\DrugBundle\Entity\Analitics;
use Vidal\DrugBundle\Entity\ATC;
use Vidal\DrugBundle\Entity\Company;
use Vidal\DrugBundle\Entity\InfoPage;
use Vidal\DrugBundle\Entity\Molecule;
use Vidal\DrugBundle\Entity\Nozology;
use Lsw\SecureControllerBundle\Annotation\Secure;

/**
 * Class AnaliticsController
 *
 * @package Vidal\MainBundle\Controller
 * @Secure(roles="ROLE_ADMIN")
 */
class AnaliticsController extends Controller
{
    /** @var \Google_Client */
    protected $client;
    /** @var \Google_Service_Analytics */
    protected $analytics;

    protected $analyticsViewId = 'ga:78472229';

    protected $metrics = 'ga:pageviews';

    /**
     * @Route("/analitics", name="analitics")
     * @Template("VidalMainBundle:Analitics:analitics.html.twig")
     */
    public function analyticsActions(Request $request)
    {
        ini_set('memory_limit', -1);
        $params = array('title' => 'Аналитика по препаратами');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Analitics $analitics */
        $analitics = $em->getRepository('VidalDrugBundle:Analitics')->get();

        $dateLast = $analitics->getDateLast()->format('d.m.Y H:i');
        $params['dateLast'] = $dateLast;

        $form = $this->createFormBuilder($analitics)
            ->add('atc', null, array('label' => 'АТХ код', 'required' => false))
            ->add('nozologies', null, array('label' => 'МКБ', 'required' => false))
            ->add('molecules', null, array('label' => 'Активные вещества', 'required' => false))
            ->add('companies', null, array('label' => 'Компания', 'required' => false))
            ->add('infoPages', null, array('label' => 'Представительство', 'required' => false))
            ->add('dateFrom', 'date', array(
                'label' => 'Аналитика с даты',
                'years' => range(date('Y') - 7, date('Y') + 1),
                'format' => 'dd MMMM yyyy',
                'constraints' => array(
                    new DateTime(array('message' => 'Дата указана в неверно')),
                )
            ))
            ->add('dateTo', 'date', array(
                'label' => 'Аналитика по дату',
                'years' => range(date('Y') - 7, date('Y') + 1),
                'format' => 'dd MMMM yyyy',
                'constraints' => array(
                    new DateTime(array('message' => 'Дата указана в неверно')),
                )
            ))
            ->add('submitProcess', 'submit', array(
                'label' => 'Обновить данные из Google Analytics',
                'attr' => array('title' => 'Последнее: ' . $dateLast, 'class' => 'btn-red')
            ))
            ->add('submit', 'submit', array('label' => 'Сохранить', 'attr' => array('class' => 'btn-red')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();

            if ($form->get('submitProcess')->isClicked()) {
                $analitics->setProcess(true);
                $em->flush();
                $this->get('session')->getFlashBag()->add('start', 'Autostart GA');
                return $this->redirect($this->generateUrl('analitics'), 301);
            }

            $this->get('session')->getFlashBag()->add('msg', 'Изменения сохранены');

            return $this->redirect($this->generateUrl('analitics'), 301);
        }

        $params['form'] = $form->createView();
        $params['analitics'] = $analitics;

        /** @var ATC[] $atcCodes */
        if ($atcCodes = $analitics->getAtc()) {
            $children = array();

            foreach ($atcCodes as $atcCode) {
                $atc = $atcCode->getATCCode();
                $children = array_merge($children, $em->getRepository('VidalDrugBundle:ATC')->findChildren($atc));
            }

            if (!empty($children)) {
                $children = array_unique($children);
                $gaChildren = $em->getRepository('VidalDrugBundle:ATC')->gaChildren($children);
                $gaCountChildren = $em->getRepository('VidalDrugBundle:ATC')->gaCountChildren($children);

                $params['atcChildren'] = $children;
                $params['atcChildrenJoined'] = implode(', ', $children);
                $params['atcGa'] = $gaChildren;
                $params['atcGaCount'] = $gaCountChildren;
            }
        }

        /** @var Nozology[] $nozologies */
        if ($nozologies = $analitics->getNozologies()) {
            $children = array();

            foreach ($nozologies as $n) {
                $code = $n->getNozologyCode();
                $children = array_merge($children, $em->getRepository('VidalDrugBundle:Nozology')->findChildren($code));
                # $children[] = $code;
            }
            $children = array_unique($children);

            if (!empty($children)) {
                $children = array_unique($children);
                $gaChildren = $em->getRepository('VidalDrugBundle:Nozology')->gaChildren($children);
                $products = $em->getRepository("VidalDrugBundle:Product")->findByNosologies($children);

                $params['nosologyChildren'] = $children;
                $params['nosologyChildrenJoined'] = implode(', ', $children);
                $params['nosologyGa'] = $gaChildren;
                $params['nosologyGaCount'] = count($products);
            }
        }

        /** @var Molecule[] $molecules */
        if ($molecules = $analitics->getMolecules()) {
            $counter = $em->getRepository('VidalDrugBundle:Molecule')->findGa($molecules);
            if (!empty($counter)) {
                $params['moleculesGa'] = $counter;
                $params['moleculesGaCount'] = $em->getRepository('VidalDrugBundle:Molecule')->findGaCount($molecules);
            }
        }

        /** @var Company[] $companies */
        if ($companies = $analitics->getCompanies()) {
            $ids = array();
            foreach ($companies as $c) {
                $ids[] = $c->getCompanyID();
            }
            $counter = $em->getRepository('VidalDrugBundle:Company')->findGa($ids);
            if (!empty($counter)) {
                $params['companyGa'] = $counter;
                $params['companyGaCount'] = $em->getRepository('VidalDrugBundle:Company')->findGaCount($ids);
            }
        }

        /** @var InfoPage[] $infoPages */
        if ($infoPages = $analitics->getInfoPages()) {
            $ids = array();
            foreach ($infoPages as $i) {
                $ids[] = $i->getInfoPageID();
            }
            $counter = $em->getRepository('VidalDrugBundle:InfoPage')->findGa($ids);
            if (!empty($counter)) {
                $params['infoPageGa'] = $counter;
                $params['infoPageGaCount'] = $em->getRepository('VidalDrugBundle:InfoPage')->findGaCount($ids);
            }
        }

        return $params;
    }

    /**
     * @Route("/analitics/atc", name="analitics_atc")
     * @Template("VidalMainBundle:Analitics:analitics_atc.html.twig")
     */
    public function analyticsAtcAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Analitics $analitics */
        $analitics = $em->getRepository('VidalDrugBundle:Analitics')->get();
        $params['analitics'] = $analitics;

        /** @var ATC[] $atc */
        if ($atc = $analitics->getAtc()) {
            $children = array();
            foreach ($atc as $a) {
                $children[] = $a->getATCCode();
            }
            if (!empty($children)) {
                $children = array_unique($children);
                $gaChildren = $em->getRepository('VidalDrugBundle:ATC')->gaChildren($children);
                $atcProducts = $em->getRepository('VidalDrugBundle:ATC')->gaChildrenProducts($children);
                $gaCountChildren = $em->getRepository('VidalDrugBundle:ATC')->gaCountChildren($children);

                $params['atcChildren'] = $children;
                $params['products'] = $atcProducts;
                $params['atcChildrenJoined'] = implode(', ', $children);
                $params['atcGa'] = $gaChildren;
                $params['atcGaCount'] = $gaCountChildren;
            }
        }

        return $params;
    }

    /**
     * @Route("/analitics/nosology", name="analitics_nosology")
     * @Template("VidalMainBundle:Analitics:analitics_nosology.html.twig")
     */
    public function analyticsNosologyAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Analitics $analitics */
        $analitics = $em->getRepository('VidalDrugBundle:Analitics')->get();
        $params['analitics'] = $analitics;

        /** @var Nozology[] $nozologies */
        if ($nozologies = $analitics->getNozologies()) {
            $children = array();
            foreach ($nozologies as $n) {
                $children[] = $n->getNozologyCode();
            }
            if (!empty($children)) {
                $children = array_unique($children);
                $gaChildren = $em->getRepository('VidalDrugBundle:Nozology')->gaChildren($children);
                $nosologyProducts = $em->getRepository('VidalDrugBundle:Nozology')->gaChildrenProducts($children);
                $gaCountChildren = $em->getRepository('VidalDrugBundle:Nozology')->gaCountChildren($children);

                $params['nosologyChildren'] = $children;
                $params['products'] = $nosologyProducts;
                $params['nosologyChildrenJoined'] = implode(', ', $children);
                $params['nosologyGa'] = $gaChildren;
                $params['nosologyGaCount'] = $gaCountChildren;
            }
        }

        return $params;
    }

    /**
     * @Route("/analitics_process", name="analitics_process")
     */
    public function analyticsProcessActions()
    {
        set_time_limit(0);
        ini_set('output_buffering', 'Off');
        ini_set('implicit_flush', 'on');
        ob_implicit_flush(true);

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
        header("X-Accel-Buffering: no");
        header('Content-Encoding: none');
        header('Content-Type: text/html; charset=utf-8');

        echo '--- STARTED ANALYTICS ---' . '<br/>' . PHP_EOL;
        ob_flush();
        flush();

        $this->initializeAnalytics();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');

        /** @var Analitics $analitics */
        $analitics = $em->getRepository("VidalDrugBundle:Analitics")->get();
        $now = new \DateTime('now');
        $analitics->setDateLast($now);
        $em->flush($analitics);

        $startDate = $analitics->getDateFrom()->format('Y-m-d');
        $endDate = $analitics->getDateTo()->format('Y-m-d');

        $em->createQuery("UPDATE VidalDrugBundle:Product p SET p.ga_pageviews = NULL")->execute();
        $products = $em->getRepository('VidalDrugBundle:Product')->findGa();

        $updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Product p
			SET p.ga_pageviews = :pageviews
			WHERE p.ProductID = :ProductID
		');
        $productsData = array();

        foreach ($products as &$product) {
            $href = empty($product['url'])
                ? "/drugs/{$product['Name']}__{$product['ProductID']}"
                : "/drugs/{$product['url']}";
            $product['href'] = $href;
            $productsData[$href] = $product;
        }

        $max = 30;
        $chunked = array_chunk($products, $max);
        $total = count($products);
        $i = 1;

        foreach ($chunked as $chunk) {
            echo '... ' . ($i * $max) . ' / ' . $total . '<br/>' . PHP_EOL;
            ob_flush();
            flush();
            $hrefs = array();

            try {
                foreach ($chunk as $product) {
                    $hrefs[] = str_replace(',', '\,', $product['href']);
                }
                $filters = 'ga:pagePath==' . implode(',ga:pagePath==', $hrefs);

                $result = $this->analytics->data_ga->get($this->analyticsViewId, $startDate, $endDate, $this->metrics, array(
                    'dimensions' => 'ga:pagePath',
                    'filters' => $filters,
                ));
                $rows = $result->getRows();

                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $href = $row[0];
                        $pageviews = $row[1];
                        $product = $productsData[$href];

                        $updateQuery->setParameter('ProductID', $product['ProductID']);
                        $updateQuery->setParameter('pageviews', $pageviews);
                        $updateQuery->execute();
                        # usleep(100 * 1000);
                    }
                }
            }
            catch (\Exception $e) {
            }

            $i++;
        }

        echo '+++ COMPLETED! +++' . '<br/>' . PHP_EOL;
        ob_flush();
        flush();

        exit;
    }

    private function initializeAnalytics()
    {
        $KEY_FILE_LOCATION = __DIR__ . DIRECTORY_SEPARATOR . 'ga.json';

        $client = new \Google_Client();
        $client->setApplicationName("Hello Analytics Reporting");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $client->addScope('https://www.googleapis.com/auth/webmasters.readonly');
        $client->addScope('https://www.googleapis.com/auth/webmasters');

        $guzzleClient = new \GuzzleHttp\Client(['verify' => false]);
        $client->setHttpClient($guzzleClient);

        $this->client = $client;
        $this->analytics = new \Google_Service_Analytics($this->client);
    }
}