<?php

namespace Vidal\DrugBundle\Controller;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vidal\DrugBundle\Entity\Document;
use Vidal\DrugBundle\Entity\Product;
use Vidal\MainBundle\Entity\KeyValue;

class ApiController extends Controller
{
    const BATCH_SIZE = 50;

    /**
     * @Route("/api/batch/item/{from}", name="api_batch_item")
     */
    public function batchNumberAction($from)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $products = $em->getRepository('VidalDrugBundle:Product')->findBatchItem($from, self::BATCH_SIZE);

        $response = new Response(json_encode($products, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');

        return $response;
    }

    /**
     * @Route("/api/batch/list", name="api_batch_list")
     */
    public function batchListAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $products = $em->getRepository('VidalDrugBundle:Product')->findBatchList();

        $maxBatches = ceil(count($products) / self::BATCH_SIZE);
        $urls = array();

        for ($i = 1; $i <= $maxBatches; $i++) {
            $urls[] = 'https://www.vidal.ru/api/batch/item/' . $i;
        }

        $response = new Response(json_encode($urls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');

        return $response;
    }

    /**
     * Рендеринг баннеров асинхронно
     */
    public function renderAnalogButtonAction(Product $product)
    {
        return $this->render('VidalDrugBundle:Api:analog_button.html.twig', array(
            'product' => $product,
        ));
    }

    /**
     * @Route("/analog/{ProductID}", name="analog")
     * @Template("VidalDrugBundle:Api:gui.html.twig")
     */
    public function guiAction($ProductID = null)
    {
        if ($ProductID) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager('drug');
            $product = $em->getRepository('VidalDrugBundle:Product')->findOneByProductID($ProductID);

            if ($product == null) {
                throw new NotFoundHttpException();
            }

            $uri = $product->getUri();
            $redirectUrl = $this->generateUrl('analog_link_uri', array('uri' => $uri));

            return $this->redirect($redirectUrl, 301);
        }

        return array(
            'ProductID' => null,
            'product' => null,
            'uniqid' => uniqid(),
            'title' => 'Сервис подбора аналогов препаратов',
        );
    }

    /**
     * @Route("/drugs/{uri}/analogs", name="analog_link_uri", requirements={"EngName"=".+"}, defaults={"ProductID" = 0}, options={"expose":true})
     * @Template("VidalDrugBundle:Api:gui.html.twig")
     */
    public function guiLinkAction(Request $request, $uri)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');

        /** @var Product $product */
        $product = $em->getRepository('VidalDrugBundle:Product')->findByUri($uri);

        $params = array(
            'title' => 'Поиск аналогов препаратов',
            'ProductID' => $product ? $product->getProductID() : null,
            'product' => $product,
            'uniqid' => uniqid(),
        );

        if (!$product) {
            return $params;
        }

        $title = 'Аналоги ' . $product->getRusName2() .' - инструкции по применению заменителей '.$product->getRusName2();
        $params['seotitle'] = $title;

        /** @var Document $documentMerge */
        if ($documentMerge = $product->getDocumentMerge()) {
            $params['documentMerge'] = $documentMerge;
            $title = 'Аналоги ' . $documentMerge->getName() . ' - инструкции по применению заменителей '. $documentMerge->getName();

            if($product->getHasChildrenMainID() && $product->getMultiForm() &&
                !in_array($documentMerge->getArticleID(), array(1,4,8))) {
                $params['seotitle'] = $title;
            }
        }

        $params['keywords'] = '';
        $params['description'] = "Аналоги ".$product->getRusName2()." - полные, групповые, нозологические заменители ".$product->getRusName2().": инструкция по применению, показания, условия хранения и срок годности";

        return $params;
    }

    /**
     * @Route("/analog-about", name="analog_about")
     * @Template("VidalDrugBundle:Api:gui.html.twig")
     */
    public function analogAboutAction()
    {
        $html = $this->renderView("VidalDrugBundle:Api:about.html.twig");

        return new JsonResponse($html);
    }

    /** @Route("/api/drug/autocomplete-product/{term}/{type}", name="api_drug_autocomplete_product", options={"expose":true}) */
    public function autocompleteProductAction(Request $request, $term, $type)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $products = $em->getRepository('VidalDrugBundle:Product')->findByTerm($term);

        return new JsonResponse($products);
    }

    /**
     * @Route("/api/drug/equal/{ProductID}", name="api_drug_equal_full_analogs")
     * @Route("/api/drug/equal-ajax/{ProductID}/{EqRateType}/{test}", name="api_drug_equal_ajax", options={"expose":true})
     */
    public function equalAjaxAction(Request $request, $ProductID, $EqRateType = 0, $test = false)
    {
        $ProductTypeCode = 'DRUG';
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $curRoute = $request->get('_route');

        $isApiRoute = false;
        if($curRoute == "api_drug_equal_full_analogs") {
            $isApiRoute = true;
            $EqRateType = 0;
        }

        // см сервис this->get('product.service')->getAnalogs  
        //  TODO объеденить
        $pdo = $em->getConnection();
        /** @var Product $product */
        $product = $em->createQuery("
            SELECT p
            FROM VidalDrugBundle:Product p
            WHERE p.ProductID = :ProductID
        ")->setParameter('ProductID', $ProductID)
            ->getOneOrNullResult();

        if ($product == null) {
            return new JsonResponse("Не найдено ни одного препарата");
        }

        $productMolecules = $product->getMolecules();
        $params = array('product' => $product);
        $params['ProductID'] = $ProductID;
        $params['EqRateType'] = $EqRateType;
        $anyProducts = array('a0' => array(), 'a1' => array(), 'a2' => array(), 'a3' => array());
        $anyProductIds = array();
        $atcs = $product->getAtcs();

        $RoaRaw = $em->createQuery("
            SELECT iroa.RouteID
            FROM VidalDrugBundle:ProductItem pri
            JOIN VidalDrugBundle:ProductItemRoute iroa
              WITH iroa.ProductID = pri.ProductID AND iroa.ItemID = pri.ItemID
            WHERE pri.ProductID = :ProductID
        ")->setParameter('ProductID', $ProductID)
            ->getResult();
        $roa = array();

        foreach ($RoaRaw as $r) {
            $roa[] = $r['RouteID'];
        }

        $roaQ = empty($roa) ? '0' : implode(',', $roa);

        # полные аналоги препарата
        if ($EqRateType == 0 || $EqRateType == 4) {
            $stmt = $pdo->prepare("
                SELECT mol.MoleculeID
                FROM product_moleculename pmn
                INNER JOIN moleculename mn ON mn.MoleculeNameID = pmn.MoleculeNameID
                INNER JOIN molecule mol ON mol.MoleculeID = mn.MoleculeID
                WHERE pmn.ProductID = $ProductID
                  AND mol.MoleculeID NOT IN (2203,1144)
            ");
            $stmt->execute();
            $midRaw = $stmt->fetchAll();
            $mid = array();

            foreach ($midRaw as $r) {
                $mid[] = $r['MoleculeID'];
            }

            $MCount = count($mid);
            if ($MCount == 0 && $EqRateType != 4) {
                return new JsonResponse("Не найдено ни одного препарата");
            }

            $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND (
                    select COUNT(pmn.MoleculeNameID)
                    from product_moleculename pmn
                    inner join moleculename mn on mn.MoleculeNameID = pmn.MoleculeNameID
                    inner join molecule mol on mol.MoleculeID = mn.MoleculeID
                    where pmn.ProductID = pr.ProductID and mol.MoleculeID not in (2203,1144)
                  ) > 0
                  AND pr.molecules = '$productMolecules'
                  AND (
                    select COUNT(*)
                    from product_item pri
                    inner join product_item_route iroa ON iroa.ProductID = pri.ProductID and iroa.ItemID = pri.ItemID
                    where pri.ProductID = pr.ProductID and iroa.RouteID in ($roaQ)
                  ) > 0
                  AND pr.MarketStatusID IN (1,2,7)
                  AND pr.ProductTypeCode NOT IN ('SUBS')
                  AND pr.IsNotForSite = 0
                  AND pr.parent_id IS NULL
                  AND pr.MainID IS NULL
                ORDER BY pr.RusName2 ASC  
            ");

            $stmt->execute();
            $products = $stmt->fetchAll();

            if ($EqRateType == 4) {
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $key = $product['ProductID'];
                        if (!in_array($key, $anyProducts)) {
                            $anyProducts['a0'][$key] = $product;
                            $anyProductIds[] = $key;
                        }
                    }
                }
            }
            else {
                $products = array('a0' => $products);
                return $this->renderProducts($products, false, $isApiRoute);
            }
        }

        # ATC связи
        $atc3 = array();
        $atc4 = array();
        $atc5 = array();

        foreach ($atcs as $atc) {
            $length = strlen($atc);
            if ($length == 4) {
                $atc3[] = $atc;
            }
            elseif ($length == 5) {
                $atc4[] = $atc;
            }
            elseif ($length >= 6) {
                $atc5[] = $atc;
            }
        }

        /* --- Групповые аналоги --- */
        if ($EqRateType == 1 || $EqRateType == 4) {
            $regexp = empty($atc5) ? '---' : implode(' |', $atc5) . ' ';
            $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND pr.atcs REGEXP '$regexp'
                  AND (
                    select COUNT(*)
                    from product_item pri
                    inner join product_item_route iroa ON iroa.ProductID = pri.ProductID and iroa.ItemID = pri.ItemID
                    where pri.ProductID = pr.ProductID and iroa.RouteID in ($roaQ)
                  ) > 0
                  AND pr.MarketStatusID IN (1,2,7)
                  AND pr.ProductTypeCode NOT IN ('SUBS')
                  AND pr.IsNotForSite = 0
                  AND pr.parent_id IS NULL
                  AND pr.MainID IS NULL
                ORDER BY pr.RusName2 ASC
            ");

            $stmt->execute();
            $products = $stmt->fetchAll();

            if ($EqRateType == 4) {
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $key = $product['ProductID'];
                        if (!in_array($key, $anyProducts)) {
                            $anyProducts['a1'][$key] = $product;
                            $anyProductIds[] = $key;
                        }
                    }
                }
            }
            else {
                $products = array('a1' => $products);
                return $this->renderProducts($products, false, $isApiRoute);
            }
        }

        /* --- Нозологические аналоги --- */
        if ($EqRateType == 2 || $EqRateType == 4) {
            $regexp = empty($atc4) ? '---' : implode(' |', $atc4) . ' ';
            $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND pr.atcs REGEXP '$regexp'
                  AND pr.MarketStatusID IN (1,2,7)
                  AND pr.ProductTypeCode NOT IN ('SUBS')
                  AND pr.IsNotForSite = 0
                  AND pr.parent_id IS NULL
                  AND pr.MainID IS NULL
                ORDER BY pr.RusName2 ASC
            ");

            $stmt->execute();
            $products = $stmt->fetchAll();

            if ($EqRateType == 4) {
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $key = $product['ProductID'];
                        if (!in_array($key, $anyProducts)) {
                            $anyProducts['a2'][$key] = $product;
                            $anyProductIds[] = $key;
                        }
                    }
                }
            }
            else {
                $products = array('a2' => $products);
                return $this->renderProducts($products, false, $isApiRoute);
            }
        }

        return $this->renderProducts($anyProducts, true, $isApiRoute);
    }

    private function renderProducts($products, $anyProducts = false, $isApiRoute = false)
    {
        $i = 1;
        $productsAll = array();
        foreach ($products as $category => $productList) {
            foreach ($productList as &$product) {
                $key = $product['ProductID'];
                if (!isset($productsAll[$key])) {
                    $product['category'] = $category;
                    $product['index'] = $i;
                    $productsAll[$key] = $product;
                    $i++;
                }
            }
        }

        $products = array_values($productsAll);
        $notFoundMsg = "Не найдено ни одного препарата";
        $apiResponse = new JsonResponse(array('error' => $notFoundMsg));

        if (empty($products)) {
            return $isApiRoute ? $apiResponse : new JsonResponse($notFoundMsg);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $productIds = array();

        foreach ($products as $product) {
            $productIds[] = intval($product['ProductID']);
        }

        $products = $em->getRepository('VidalDrugBundle:Product')->findByProductIds($productIds);
        if (empty($products)) {
            return $isApiRoute ? $apiResponse : new JsonResponse($notFoundMsg);
        }

        $productCategories = array();
        foreach ($products as &$product) {
            $key = $product['ProductID'];
            $category = $productsAll[$key]['category'];
            $product['category'] = $category;
            $product['index'] = $productsAll[$key]['index'];

            if (!isset($productCategories[$category])) {
                $productCategories[$category] = array();
            }
            $productCategories[$category][] = $isApiRoute ? $product['ProductID'] : $product;
        }

        usort($products, function ($p1, $p2) {
            return $p1['index'] > $p2['index'];
        });
        ksort($productCategories);

        if ($isApiRoute) {
            return new JsonResponse($productCategories);
        }

        $params = array('products' => $products);
        $params['productCategories'] = $productCategories;
        $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
        $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);

        foreach ($products as &$product) {
            $key = $product['ProductID'];
            $product['category'] = $productsAll[$key]['category'];
        }

        $html = $anyProducts
            ? $this->renderView('VidalDrugBundle:Api:render_products.html.twig', $params)
            : $this->renderView('VidalDrugBundle:Vidal:render_products.html.twig', $params);

        return new JsonResponse($html);
    }
}
