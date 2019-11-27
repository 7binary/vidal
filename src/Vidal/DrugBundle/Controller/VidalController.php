<?php

namespace Vidal\DrugBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vidal\DrugBundle\Entity\Document;
use Vidal\DrugBundle\Entity\InfoPage;
use Vidal\DrugBundle\Entity\Molecule;
use Vidal\DrugBundle\Entity\Product;
use Vidal\DrugBundle\Entity\RiglaRegion;
use Vidal\MainBundle\Controller\BannerController;
use Vidal\MainBundle\Entity\Banner;
use Vidal\MainBundle\Entity\ProtecProduct;
use Vidal\MainBundle\Geo\IPGeoBase;

class VidalController extends Controller
{
    const PRODUCTS_PER_PAGE = 40;
    const COMPANIES_PER_PAGE = 50;
    const MOLECULES_PER_PAGE = 50;

    private $letters = array('a' => 'А', 'b' => 'Б', 'v' => 'В', 'g' => 'Г', 'd' => 'Д', 'zh' => 'Ж', 'z' => 'З', 'i' => 'И', 'j' => 'Й', 'k' => 'К', 'l' => 'Л', 'm' => 'М', 'n' => 'Н', 'o' => 'О', 'p' => 'П', 'r' => 'Р', 's' => 'С', 't' => 'Т', 'u' => 'У', 'f' => 'Ф', 'h' => 'Х', 'c' => 'Ц', 'ch' => 'Ч', 'sh' => 'Ш', 'je' => 'Э', 'ju' => 'Ю', '8' => '8');
    private $molecule_letters = array('a' => 'А', 'b' => 'Б', 'v' => 'В', 'g' => 'Г', 'd' => 'Д', 'e' => 'Е', 'z' => 'З', 'i' => 'И', 'j' => 'Й', 'k' => 'К', 'l' => 'Л', 'm' => 'М', 'n' => 'Н', 'o' => 'О', 'p' => 'П', 'r' => 'Р', 's' => 'С', 't' => 'Т', 'u' => 'У', 'f' => 'Ф', 'h' => 'Х', 'c' => 'Ц', 'ch' => 'Ч', 'sh' => 'Ш', 'je' => 'Э', 'ja' => 'Я', 'n-eng' => 'N');

    /** @Route("/poisk_preparatov") */
    public function r1()
    {
        return $this->redirect($this->generateUrl('drugs'), 301);
    }

    /** @Route("/BAD/opisanie/{url}") */
    public function r4($url = null)
    {
        return $this->redirect($this->generateUrl('drugs'), 301);
    }

    /** @Route("/patsientam/spisok-boleznei-po-alfavitu/") */
    public function r5()
    {
        return $this->redirect($this->generateUrl('disease'), 301);
    }

    /** @Route("/poisk_preparatov/fir_{url}", requirements={"url"=".+"}) */
    public function redirectFirm($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        $CompanyID = $url;
        $em = $this->getDoctrine()->getManager('drug');
        $company = $em->getRepository('VidalDrugBundle:Company')->findByCompanyID($CompanyID);

        if ($company == null) {
            throw $this->createNotFoundException();
        }

        $products = $em->getRepository('VidalDrugBundle:Product')->findByOwner($CompanyID);

        if (empty($products)) {
            $firstLetter = mb_substr($company['CompanyName'], 0, 1);
            return $this->redirect($this->generateUrl('companies', array('l' => $firstLetter)), 301);
        }

        return $this->redirect($this->generateUrl('firm_item', array('CompanyID' => $url)), 301);
    }

    /** @Route("/poisk_preparatov/lfir_{url}", requirements={"url"=".+"}) */
    public function redirectLfirm($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        $CompanyID = $url;
        $em = $this->getDoctrine()->getManager('drug');
        $company = $em->getRepository('VidalDrugBundle:Company')->findByCompanyID($CompanyID);

        if ($company == null) {
            throw $this->createNotFoundException();
        }

        $products = $em->getRepository('VidalDrugBundle:Product')->findByOwner($CompanyID);

        if (empty($products)) {
            $firstLetter = mb_substr($company['CompanyName'], 0, 1);
            return $this->redirect($this->generateUrl('companies', array('l' => $firstLetter)), 301);
        }

        return $this->redirect($this->generateUrl('firm_item', array('CompanyID' => $url)), 301);
    }

    /**
     * Список препаратов по компании
     *
     * @Route("/drugs/firm/{CompanyID}", name="firm_item", requirements={"CompanyID":"\d+"})
     * @Template("VidalDrugBundle:Vidal:firm_item.html.twig")
     */
    public function firmItemAction($CompanyID)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $company = $em->getRepository('VidalDrugBundle:Company')->findByCompanyID($CompanyID);

        if ($company == null) {
            throw $this->createNotFoundException();
        }

        $products = $em->getRepository('VidalDrugBundle:Product')->findByOwner($CompanyID);

        if (empty($products)) {
            $firstLetter = mb_substr($company['CompanyName'], 0, 1);

            return $this->redirect($this->generateUrl('companies', array('l' => $firstLetter)), 301);
        }

        # находим представительства
        $productsRepresented = array();
        for ($i = 0; $i < count($products); $i++) {
            $key = $products[$i]['InfoPageID'];
            if (!empty($key) && !isset($productsRepresented[$key])) {
                $productsRepresented[$key] = $products[$i];
            }
        }

        $params = array(
            'title' => $this->strip($company['CompanyName']) . ' | Фирмы-производители',
            'company' => $company,
            'productsRepresented' => $productsRepresented,
            'products' => $products,
        );

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
        }

        return $params;
    }

    /**
     * Список препаратов по клиннико-фармакологической группе
     *
     * @Route("/drugs/cl-ph-group/{description}", name="clphgroup")
     * @Template("VidalDrugBundle:Vidal:clphgroup.html.twig")
     */
    public function clphgroupAction($description)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $products = $em->getRepository('VidalDrugBundle:Product')->findByClPhGroup($description);
        $params = array(
            'products' => $products,
            'description' => $description,
            'title' => 'Клинико-фармакологическая группа',
        );

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
        }

        return $params;
    }

    /** @Route("/poisk_preparatov/inf_{url}", requirements={"url"=".+"}) */
    public function redirectInfopage($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        return $this->redirect($this->generateUrl('inf_item', array('InfoPageID' => $url)), 301);
    }

    /** @Route("/poisk_preparatov/linf_{url}", requirements={"url"=".+"}) */
    public function redirectLInfopage($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        return $this->redirect($this->generateUrl('inf_item', array('InfoPageID' => $url)), 301);
    }

    /**
     * Страничка представительства и список препаратов
     *
     * @Route("/drugs/company/{InfoPageID}", name="inf_item", requirements={"InfoPageID":"\d+"})
     * @Template("VidalDrugBundle:Vidal:inf_item.html.twig")
     */
    public function infItemAction($InfoPageID)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var InfoPage $infoPage */
        $infoPage = $em->getRepository('VidalDrugBundle:InfoPage')->findOneByInfoPageID($InfoPageID);

        if (!$infoPage || $infoPage->getCountProducts() == 0) {
            throw $this->createNotFoundException();
        }

        $picture = $em->getRepository('VidalDrugBundle:Picture')->findByInfoPageID($InfoPageID);
        $params = array(
            'infoPage' => $infoPage,
            'picture' => $picture,
            'title' => $this->strip($infoPage->getRusName()) . ' | Информационные страницы',
            'portfolios' => $em->getRepository('VidalDrugBundle:InfoPage')->findPortfolios($InfoPageID),
        );

        $products = $em->getRepository('VidalDrugBundle:Product')->findByInfoPageID($InfoPageID);

        if (!empty($products)) {
            $productsBads = array();
            $productsLp = array();
            $productsMi = array();
            $productsLk = array();
            $productsPara = array();
            $productsNutr = array();

            foreach ($products as $product) {
                if ($product['ProductTypeCode'] == Product::TYPE_BAD) {
                    $productsBads[] = $product;
                }
                elseif ($product['ProductTypeCode'] == Product::TYPE_MI) {
                    $productsMi[] = $product;
                }
                elseif ($product['ProductTypeCode'] == Product::TYPE_COSM) {
                    $productsLk[] = $product;
                }
                elseif ($product['ProductTypeCode'] == Product::TYPE_PARA) {
                    $productsPara[] = $product;
                }
                elseif ($product['ProductTypeCode'] == Product::TYPE_NUTR) {
                    $productsNutr[] = $product;
                }
                else {
                    $productsLp[] = $product;
                }
            }

            $params['productsBads'] = $productsBads;
            $params['productsLk'] = $productsLk;
            $params['productsLp'] = $productsLp;
            $params['productsMi'] = $productsMi;
            $params['productsPara'] = $productsPara;
            $params['productsNutr'] = $productsNutr;

            $productIds = $this->getProductIds($products);
            $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
        }

        return $params;
    }

    /**
     * @Route("/drugs/molecules", name="molecules")
     * @Template("VidalDrugBundle:Vidal:molecules.html.twig")
     */
    public function moleculesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $q = $request->query->get('q', null);
        $l = $request->query->get('l', null);
        $p = $request->query->get('p', 1);

        if ($l) {
            $query = $em->getRepository('VidalDrugBundle:Molecule')->getQueryByLetter($l);
        }
        elseif ($q) {
            $query = $em->getRepository('VidalDrugBundle:Molecule')->getQueryByString($q);
        }
        else {
            $query = $em->getRepository('VidalDrugBundle:Molecule')->getQuery();
        }

        $params = array(
            'menu_drugs' => 'molecule',
            'title' => 'Активные вещества',
            'q' => $q,
            'l' => $l,
            'pagination' => $this->get('knp_paginator')->paginate($query, $p, self::MOLECULES_PER_PAGE),
        );

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        return $params;
    }

    /**
     * Функция отображения веществ по букве
     * @Route("/drugs/molecules/bukva/{letter}", name="drug_molecules_letter")
     * @Template("VidalDrugBundle:Vidal:molecules_letter.html.twig")
     */
    public function moleculesLetterAction(Request $request, $letter)
    {
        if (!isset($this->molecule_letters[$letter])) {
            throw $this->createNotFoundException();
        }

        $l = $this->molecule_letters[$letter];

        $em = $this->getDoctrine()->getManager('drug');
        $p = $request->query->get('p', null); // номер страницы

        if ($p == 1) {
            return $this->redirect($this->generateUrl('drug_molecules_letter', array('letter' => $letter)), 301);
        }
        elseif ($p == null) {
            $p = 1;
        }

        $query = $em->getRepository('VidalDrugBundle:Molecule')->getQueryByLetter($l);

        $params = array(
            'letter' => $letter,
            'l' => $l,
            'title' => 'Активные вещества на букву ' . $l,
            'vetpage' => true,
            'pagination' => $this->get('knp_paginator')->paginate($query, $p, 50),
        );

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        $params['seotitle'] = 'Активые вещества на букву ' . $l;
        $params['keywords'] = '';

        return $params;
    }

    /** @Route("/poisk_preparatov/act_{url}", requirements={"url"=".+"}) */
    public function redirectMolecule($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        return $this->redirect($this->generateUrl('molecule', array('MoleculeID' => $url)), 301);
    }

    /**
     * Список препаратов по активному веществу: одно-монокомпонентные
     * @Route("/drugs/molecule/{MoleculeID}/{search}", name="molecule", requirements={"MoleculeID":"\d+"})
     * @Template("VidalDrugBundle:Vidal:molecule.html.twig")
     */
    public function moleculeAction($MoleculeID, $search = 0)
    {
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Molecule $molecule */
        $molecule = $em->getRepository('VidalDrugBundle:Molecule')->findByMoleculeID($MoleculeID);

        if (!$molecule) {
            throw $this->createNotFoundException();
        }

        $hasProducts = $em->getRepository('VidalDrugBundle:Product')->hasMoleculeID($MoleculeID);

        /** @var Document $document */
        $document = $em->getRepository('VidalDrugBundle:Document')->findByMoleculeID($MoleculeID);
        $params = array(
            'molecule' => $molecule,
            'document' => $document,
            'title' => mb_strtoupper($molecule->getTitle(), 'utf-8') . ' | Активные вещества',
        );

        $description = $this->mb_ucfirst($this->strip($molecule->getLatName()))
            . ' (' . $this->mb_ucfirst($this->strip($molecule->getRusName())) . ')';

        if ($document) {
            $description .= ' ' . $this->truncateHtml($document->getPhInfluence(), 180);
        }

        $params['description'] = $description;
        $params['hasProducts'] = $hasProducts;

        return $search ? $this->render('VidalDrugBundle:Vidal:search_molecule.html.twig', $params) : $params;
    }

    private function truncateHtml($text, $length = 100)
    {
        return mb_substr(strip_tags($text), 0, $length, 'UTF-8');
    }

    /** @Route("/poisk_preparatov/lact_{url}", requirements={"url"=".+"}) */
    public function redirectLMolecule($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        return $this->redirect($this->generateUrl('molecule_included', array('MoleculeID' => $url)), 301);
    }

    /**
     * Отображение списка препаратов, в состав которых входит активное вещество (Molecule)
     *
     * @Route("/drugs/molecule-in/{MoleculeID}", name="molecule_included", requirements={"MoleculeID":"\d+"})
     * @Template("VidalDrugBundle:Vidal:molecule_included.html.twig")
     */
    public function moleculeIncludedAction($MoleculeID)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $molecule = $em->getRepository('VidalDrugBundle:Molecule')->findByMoleculeID($MoleculeID);

        if (!$molecule) {
            throw $this->createNotFoundException();
        }

        # все продукты по активному веществу и отсеиваем дубли
        $productsRaw = $em->getRepository('VidalDrugBundle:Product')->findByMoleculeID($MoleculeID);

        if (empty($productsRaw)) {
            return array('molecule' => $molecule);
        }

        $products = array();
        $productIds = array();

        for ($i = 0; $i < count($productsRaw); $i++) {
            $key = $productsRaw[$i]['ProductID'];

            if (!isset($products[$key])) {
                $products[$key] = $productsRaw[$i];
                $productIds[] = $key;
            }
        }

        # препараты надо разбить на монокомнонентные и многокомпонентные группы
        $components = $em->getRepository('VidalDrugBundle:Molecule')->countComponents($productIds);
        $products1 = array();
        $products2 = array();

        foreach ($products as $id => $product) {
            $components[$id] == 1
                ? $products1[$id] = $product
                : $products2[$id] = $product;
        }

        uasort($products1, array($this, 'sortProducts'));
        uasort($products2, array($this, 'sortProducts'));

        $description = 'Инструкции лекарственных препаратов, содержащих активное вещество ';
        $description .= $this->mb_ucfirst($this->strip($molecule->getLatName()))
            . ' (' . $this->mb_ucfirst($this->strip($molecule->getRusName())) . ')';

        $description .= ' в справочнике лекарственных препаратов Видаль: наименование, форма выпуска и дополнительная информация';

        return array(
            'description' => $description,
            'molecule' => $molecule,
            'products1' => $products1,
            'products2' => $products2,
            'companies' => $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds),
            'infoPages' => $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($productsRaw),
            'title' => mb_strtoupper($molecule->getTitle(), 'utf-8') . ' | Активные вещества в препаратах',
        );
    }

    /**
     * Страничка рассшифровки МНН аббревиатур
     *
     * @Route("drugs/gnp", name="gnp")
     * @Route("poisk_preparatov/gnp.{ext}", name="gnp_old", defaults={"ext"="htm"})
     * @Template("VidalDrugBundle:Vidal:gnp.html.twig")
     */
    public function gnpAction(Request $request)
    {
        if ($request->get('_route') == 'gnp_old') {
            return $this->redirect($this->generateUrl('gnp'));
        }

        $em = $this->getDoctrine()->getManager('drug');

        $params = array(
            'title' => 'Международные наименования - МНН',
            'gnps' => $em->getRepository('VidalDrugBundle:MoleculeBase')->findAll(),
        );

        return $params;
    }

    /**
     * @Route("/drugs/product-group/{ids}", name="product-group")
     * @Template("VidalDrugBundle:Vidal:product_group.html.twig")
     */
    public function productGroupAction($ids)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $ids = explode('-', $ids);
        $products = array();
        $productIds = array();

        $params = array();

        foreach ($ids as $id) {
            $id = intval($id);
            $productIds[] = $id;
            $products[] = $em->getRepository('VidalDrugBundle:Product')->findFieldsByProductID($id);
        }

        $params['products'] = $products;
        $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
        $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);

        return $params;
    }

    /**
     * Клик по "Купить"
     * @Route("/protec-clicked/{id}", name="protec_clicked", requirements={"id":"\d+"}, options={"expose":true})
     */
    public function protecClickedAction(Request $request, $id)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $emMain */
        $emMain = $this->getDoctrine()->getManager();
        /** @var ProtecProduct $product */
        $product = $emMain->getRepository('VidalMainBundle:ProtecProduct')->findOneById($id);

        if ($product == null) {
            throw $this->createNotFoundException();
        }

        $counter = $product->getClicked();
        $product->setClicked($counter + 1);
        $emMain->flush($product);

        return new JsonResponse('OK');
    }

    /**
     * Аптека к препарату
     * @Route("/protec/{ProductID}", name="protec", requirements={"ProductID":"\d+"}, options={"expose":true})
     */
    public function protecAction(Request $request, $ProductID)
    {
        if ($this->container->getParameter('kernel.environment') == 'prod' && $request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var EntityManager $emMain */
        $emMain = $this->getDoctrine()->getManager();
        /** @var Product $product */
        $product = $em->getRepository('VidalDrugBundle:Product')->findByProductID($ProductID);

        if (!$product || $product->getInactive()) {
            throw $this->createNotFoundException();
        }

        $params = array('product' => $product, 'region' => null, 'products' => array());

        $env = $this->container->getParameter("kernel.environment");
        $ip = $env == 'dev' ? '5.8.177.240' : $this->container->get('request')->getClientIp();
        $protecIp = $emMain->getRepository('VidalMainBundle:ProtecIp')->get($ip);
        $regionTitle = $protecIp->getRegion();

        $region = $emMain->getRepository("VidalMainBundle:ProtecRegion")->get($regionTitle);
        $regionFound = true;
        $regionNotFound = '';

        if (empty($region) || $protecIp->getCountry() != 'RU') {
            $regionFound = false;
            $regionNotFound = $regionTitle;
            $region = $emMain->getRepository("VidalMainBundle:ProtecRegion")->get('Москва');
        }

        $productIds = $em->getRepository("VidalDrugBundle:Product")->findFamilyProductIds($ProductID);
        $products = $emMain->getRepository("VidalMainBundle:ProtecProduct")->getByRegionProductID($region, $productIds);
        $params['region'] = $region;
        $params['products'] = $products;
        $params['regionFound'] = $regionFound;
        $params['regionTitle'] = $region->getTitle();
        $params['regionNotFound'] = $regionNotFound;
        $params['regionIp'] = $ip;

        $productUrl = $product->getUrl();
        $url = empty($productUrl)
            ? "https://www.vidal.ru/drugs/{$product->getName()}__{$product->getProductID()}"
            : "https://www.vidal.ru/drugs/{$productUrl}";
        $append = '?utm_source=vidal&utm_medium=referral&utm_campaign=' . $url;
        $params['append'] = $append;

        $html = $this->renderView('VidalDrugBundle:Vidal:protec.html.twig', $params);

        return new JsonResponse($html);
    }

    /**
     * Статьи и материалы к препарату
     * @Route("/drugs/documents-of-product/{ProductID}", name="documents_of_product", requirements={"ProductID":"\d+"}, options={"expose":true})
     */
    public function documentsOfProductAction(Request $request, $ProductID)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Product $product */
        $product = $em->getRepository('VidalDrugBundle:Product')->findByProductID($ProductID);

        if (!$product || $product->getInactive()) {
            throw $this->createNotFoundException();
        }

        $params = array('product' => $product);
        $params['publicationsByProduct'] = $em->getRepository('VidalDrugBundle:Product')->publicationsByProduct($ProductID);
         $params['publicationsByMolecule'] = $em->getRepository('VidalDrugBundle:Product')->publicationsByMolecule($ProductID);
        $params['articlesByProduct'] = $em->getRepository('VidalDrugBundle:Product')->articlesByProduct($ProductID);
        $params['articlesByMolecule'] = $em->getRepository('VidalDrugBundle:Product')->articlesByMolecule($ProductID);
        $params['artsByProduct'] = $em->getRepository('VidalDrugBundle:Product')->artsByProduct($ProductID);
        $params['artsByMolecule'] = $em->getRepository('VidalDrugBundle:Product')->artsByMolecule($ProductID);

        $atcCodes = $em->getRepository('VidalDrugBundle:Product')->findAllATC($product);
        if (count($atcCodes) > 0) {
            $params['publicationsByAtc'] = $em->getRepository('VidalDrugBundle:Product')->publicationsByAtc($atcCodes);
            $params['articlesByAtc'] = $em->getRepository('VidalDrugBundle:Product')->articlesByAtc($atcCodes);
            $params['artsByAtc'] = $em->getRepository('VidalDrugBundle:Product')->artsByAtc($atcCodes);
        }

        $html = $this->renderView('VidalDrugBundle:Vidal:documents_of_product.html.twig', $params);

        return new JsonResponse($html);
    }

    /**
     * Описание препарата
     * @Route("/drugs/{EngName}", name="product_url", requirements={"EngName"=".+"}, defaults={"ProductID" = 0}, options={"expose":true})
     * @Template("VidalDrugBundle:Vidal:document.html.twig")
     */
    public function productAction(Request $request, $EngName)
    {
        $isIdRoute = strpos($EngName, '__') !== false;

        if ($isIdRoute) {
            list($Name, $ProductID) = explode('__', $EngName);
            if (in_array($ProductID, array(40431, 40433, 40432, 32336, 42446))) {
                return $this->redirect($this->generateUrl('product_url', array(
                    'EngName' => 'prevenar_13__32333',
                )), 301);
            }
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var EntityManager $emDefault */
        $emDefault = $this->getDoctrine()->getManager();

        $ProductID = $emDefault->getRepository("VidalMainBundle:DrugInfo")->findProductID($EngName);

        if (null == $ProductID && $isIdRoute) {
            list($Name, $ProductID) = explode('__', $EngName);
        }

        /** @var Product $product */
        $product = $ProductID == null ?false: $em->getRepository('VidalDrugBundle:Product')->findByProductID($ProductID);

        if (!$product) {
            throw $this->createNotFoundException();
        }

        # если в админке указан вручную редирект этого препарата на другой
        if ($redirectId = $product->getRedirectId()) {
            /** @var Product $mainProduct */
            if ($mainProduct = $em->getRepository('VidalDrugBundle:Product')->findOneByProductID($redirectId)) {
                $uri = $mainProduct->getUri();
                $redirectUrl = $this->generateUrl('product_url', array('EngName' => $uri));
                return $this->redirect($redirectUrl, 301);
            }
            else {
                throw $this->createNotFoundException();
            }
        }

        if ($product->getInactive()) {
            throw $this->createNotFoundException();
        }

        # REDIRECT BY PARENT
        try {
            /** @var Product $parentProduct */
            if ($parentProduct = $product->getParent()) {
                $uri = $parentProduct->getUri();
                $redirectUrl = $this->generateUrl('product_url', array('EngName' => $uri));
                return $this->redirect($redirectUrl, 301);
            }

            # REDIRECT BY MainID
            $MainID = $product->getMainID();
            if (!empty($MainID)) {
                /** @var Product $mainProduct */
                if ($mainProduct = $em->getRepository('VidalDrugBundle:Product')->findOneByProductID($MainID)) {
                    $uri = $mainProduct->getUri();
                    $redirectUrl = $this->generateUrl('product_url', array('EngName' => $uri));
                    return $this->redirect($redirectUrl, 301);
                }
                else {
                    throw $this->createNotFoundException();
                }
            }

            # редирект при ProductID и несоответствии первой части - БАГ, проверить и поправить
//            $prodName = $product->getName();
//            if ($isIdRoute && !empty($Name) && $prodName != $Name && $prodName != ($Name.'_')) {
//                $uri = $product->getUri();
//                $redirectUrl = $this->generateUrl('product_url', array('EngName' => $uri));
//                return $this->redirect($redirectUrl, 301);
//            }
        }
        catch (\Exception $e) {
            // temporary catch
        }

        if (!in_array($product->getMarketStatusID()->getMarketStatusID(), array(1, 2, 7)) || $product->getInactive()) {
            throw $this->createNotFoundException();
        }

        $params = array();
        $document = $product->getDocument();

        # условите от Марии, что бады должны иметь Document.ArticleID = 6
        if ($product->getProductTypeCode() == 'BAD' && $document && $document->getArticleID() != 6) {
            $document = null;
        }

        if ($document) {
            $documentId = $document->getDocumentID();
            $params['document'] = $document;
            $params['nozologies'] = $em->getRepository('VidalDrugBundle:Nozology')->findByDocumentID($documentId);
            $params['parentATCCode'] = $em->getRepository('VidalDrugBundle:ATC')->getParent($product);
        }

        $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($product);

        if ($documentMerge = $product->getDocumentMerge()) {
            $params['documentMerge'] = $documentMerge;
        }

        # если препараты склеены по ParentID, то нужно выводить каждое описание по порядку
        if ($product->getHasChildrenParentID() || $product->getHasChildrenMainID()) {
            $compositions = array();
            $forms = json_decode($product->getForms(), true);

            if (!empty($forms)) {
                foreach ($forms as $form) {
                    /** @var Product $productChild */
                    if ($productChild = $em->getRepository('VidalDrugBundle:Product')->findOneByProductID($form['ProductID'])) {
                        $compositions[] = $productChild->getComposition();
                    }
                }
                $params['compositions'] = array_unique($compositions);
            }
        }

        # крайне медленно и затратно для базы данных высчитывать аналоги препаратов находу, надо заранее просчитывать их, как раньше было
        if ($product->getProductTypeCode() == Product::TYPE_DRUG) {
            $productAnalogs = $this->get('product.service')->getAnalogs($product, 4);

            $productAnalogUnique = [];
            if(isset($productAnalogs['a0']) && count($productAnalogs['a0']) > 0) {
                foreach($productAnalogs['a0'] as $productAnalog) {
                    if(!isset($productAnalog['CountryName'])) {
                        $productAnalog['CountryName'] = "";
                    }
                    $unique = $productAnalog['RusName'].$productAnalog['CountryName'].$productAnalog['GDDBName'];
                    $productAnalogUnique[$unique] = $productAnalog;
                }
            }

            $params['productAnalogs'] = $productAnalogUnique;
        }

        $productId = $product->getProductID();
        $productIds = array($productId);
        $atcCodes = $em->getRepository('VidalDrugBundle:Product')->findAllATC($product);

        $params['product'] = $product;
        $params['productPage'] = true;
        $params['isIdRoute'] = $isIdRoute;
        $params['productAtcCodes'] = $atcCodes;

        $altTitle = preg_replace('/<sup\b[^>]*>(.*?)<\/sup>/i', '', $product->getRusName());
        $altTitle = mb_strtolower($altTitle, 'utf-8') . ' инструкция по применению';
        $altTitle = $this->mb_ucfirst($altTitle);
        $params['img_alt_title'] = $altTitle;

        $params['products'] = array($product);
        $params['owners'] = $em->getRepository('VidalDrugBundle:Company')->findOwnersByProducts($productIds);
        $params['distributors'] = $em->getRepository('VidalDrugBundle:Company')->findDistributorsByProducts($productIds);
        $params['molecules'] = $em->getRepository('VidalDrugBundle:Molecule')->findByProductID($productId);

        $title = $this->strip($product->getRusName());
        $params['ogTitle'] = $title;
        $params['zip'] = $this->strip($product->getZipInfo());

        # тз Сеошника - description
        $description = $product->getDescription();
        $params['description'] = $description;
        $params['banner_mkb'] = null;
        $params['banners_atc'] = null;

        # находим ADS-блоки VidalBox
        $params['ads_vidalbox'] = $em->getRepository("VidalDrugBundle:Ads")->findbyProduct($product);

        # баннер по МКБ
        $file = $this->container->getParameter('download_dir') . DIRECTORY_SEPARATOR . 'banner_mkb.json';
        $ids = @json_decode(file_get_contents($file), true);
        if (!empty($ids) && in_array($product->getProductID(), $ids)) {
            $emDefault = $this->getDoctrine()->getManager('default');
            /** @var Banner $bannerMkb */
            if ($bannerMkb = $emDefault->getRepository("VidalMainBundle:Banner")->getBannerMkb()) {
                BannerController::$used[] = $bannerMkb->getId();
                BannerController::$exclude_banner_mkb = false;
                $params['banner_mkb'] = $bannerMkb->getTitle();
            }
        }

        $params['ProductID'] = $product->getProductID();

        # лечебная косметика выводятся по-другому
        if ($product->isCosm()) {
            $params['seotitle'] = $title
                . ' ' . $this->strip($product->getEngName())
                . ' ' . $product->getZipInfo()
                . ' (' . $product->getId().')'
                . ' - справочник препаратов и лекарств';
            $params['isCosm'] = true;
            $params['keywords'] = "";

            return $this->render("VidalDrugBundle:Vidal:bad_document.html.twig", $params);
        }

        # медицинские изделия выводятся по-другому
        if ($product->isMI()) {
            $params['seotitle'] = $title
                . ' ' . $this->strip($product->getEngName())
                . ' ' . $product->getZipInfo()
                . ' (' . $product->getId().')'
                . ' - справочник препаратов и лекарств';
            $params['isMI'] = true;
            $params['keywords'] = "";

            return $this->render("VidalDrugBundle:Vidal:bad_document.html.twig", $params);
        }

        # БАДы выводятся по-другому
        if ($product->isBAD() || ($document && $document->isBAD())) {
            $params['seotitle'] = $title
                . ' ' . 'инструкция по применению: показания, противопоказания, побочное действие – описание'
                . ' ' . $this->strip($product->getEngName())
                . ' ' . $product->getZipInfo()
                . ' (' . $product->getId().')'
                . ' - справочник препаратов и лекарств';
            $params['keywords'] = "";

            return $this->render("VidalDrugBundle:Vidal:bad_document.html.twig", $params);
        }

        # Питание выводится по-другому
        if ($product->isNutr()) {
            $params['seotitle'] = $title
                . ' ' . 'инструкция по применению: показания, противопоказания, побочное действие – описание'
                . ' ' . $this->strip($product->getEngName())
                . ' ' . $product->getZipInfo()
                . ' (' . $product->getId().')'
                . ' - справочник препаратов и лекарств';
            $params['keywords'] = "";

            return $this->render("VidalDrugBundle:Vidal:nutr_document.html.twig", $params);
        }

        $params['seotitle'] = $title
            . ' ' . 'инструкция по применению: показания, противопоказания, побочное действие – описание'
            . ' ' . $this->strip($product->getEngName())
            . ' ' . $product->getZipInfo()
                . ' (' . $product->getId().')'
            . ' - справочник препаратов и лекарств';
        $params['keywords'] = "";

        # SUBS redirects
        if (in_array($product->getProductTypeCode(), array('SUBS', 'SRED'))) {
            return $this->redirectSubs($product, $em);
        }

        # RIGLA
        $params['riglaPrice'] = null;

        return $params;
    }

    private function redirectSubs(Product $product, EntityManager $em)
    {
        $rusName = $product->getRusName2();
        /** @var Product $sameProduct */
        $sameProduct = $em->getRepository('VidalDrugBundle:Product')->findSame($rusName);

        if ($sameProduct == null) {
            return $this->redirect($this->generateUrl('drugs'), 301);
        }

        return $this->redirect($this->generateUrl('product_url', array(
            'EngName' => $sameProduct->getName() . '__' . $sameProduct->getProductID(),
        )), 301);
    }

    /** @Route("/poisk_preparatov/{name}.htm", requirements={"name":"[^~]+"}) */
    public function moleculeRedirect($name)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $molecule = $em->getRepository('VidalDrugBundle:Molecule')->findByName($name);

        if (!$molecule) {
            return $this->redirect($this->generateUrl('drugs'), 301);
        }

        return $this->redirect($this->generateUrl('molecule', array('MoleculeID' => $molecule['MoleculeID'])), 301);
    }

    public function mb_ucfirst($str, $enc = 'utf-8')
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc) . mb_strtolower(mb_substr($str, 1, mb_strlen($str, $enc), $enc), $enc);
    }

    public function riglaAction($riglaPrice, $product)
    {
        $params['riglaPrice'] = $riglaPrice;
        $params['product'] = $product;

        return $this->render('VidalDrugBundle:Vidal:rigla.html.twig', $params);
    }

    public function riglaBuyAction($riglaPrice, $product)
    {
        $params['riglaPrice'] = $riglaPrice;
        $params['product'] = $product;

        return $this->render('VidalDrugBundle:Vidal:rigla_buy.html.twig', $params);
    }

    /** Получить массив идентификаторов продуктов */
    private function getProductIds($products)
    {
        $productIds = array();

        foreach ($products as $product) {
            $productIds[] = isset($product['ProductID']) ? $product['ProductID'] : $product->getProductID();
        }

        return $productIds;
    }

    /** Отсортировать препараты по имени */
    private function sortProducts($a, $b)
    {
        return strcasecmp($a['RusName'], $b['RusName']);
    }

    private function strip($string)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));

        return trim(str_replace(explode(' ', '® ™'), '', $string));
    }
}
