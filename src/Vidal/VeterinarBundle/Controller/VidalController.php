<?php

namespace Vidal\VeterinarBundle\Controller;

use Doctrine\ORM\EntityManager;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vidal\DrugBundle\Entity\Publication;
use Vidal\VeterinarBundle\Command\ProductNameCommand;
use Vidal\VeterinarBundle\Entity\Company;
use Vidal\VeterinarBundle\Entity\InfoPage;
use Vidal\VeterinarBundle\Entity\Product;

class VidalController extends Controller
{
    const PRODUCTS_PER_PAGE = 40;
    const PUBLICATIONS_PER_PAGE = 20;

    private $letters = array('a' => 'А', 'b' => 'Б', 'v' => 'В', 'g' => 'Г', 'd' => 'Д', 'zh' => 'Ж', 'z' => 'З', 'i' => 'И', 'j' => 'Й', 'k' => 'К', 'l' => 'Л', 'm' => 'М', 'n' => 'Н', 'o' => 'О', 'p' => 'П', 'r' => 'Р', 's' => 'С', 't' => 'Т', 'u' => 'У', 'f' => 'Ф', 'h' => 'Х', 'c' => 'Ц', 'ch' => 'Ч', 'sh' => 'Ш', 'je' => 'Э', 'ju' => 'Ю', '8' => '8');
    private $molecule_letters = array('a' => 'А', 'b' => 'Б', 'v' => 'В', 'g' => 'Г', 'd' => 'Д', 'e' => 'Е', 'z' => 'З', 'i' => 'И', 'j' => 'Й', 'k' => 'К', 'l' => 'Л', 'm' => 'М', 'n' => 'Н', 'o' => 'О', 'p' => 'П', 'r' => 'Р', 's' => 'С', 't' => 'Т', 'u' => 'У', 'f' => 'Ф', 'h' => 'Х', 'c' => 'Ц', 'ch' => 'Ч', 'sh' => 'Ш', 'je' => 'Э', 'ja' => 'Я', 'n-eng' => 'N');

    /**
     * @Route("/veterinar/novosti/{id}", name="v_publication")
     * @Template("VidalVeterinarBundle:Vidal:publication.html.twig")
     */
    public function publicationAction(Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var Publication $publication */
        $publication = $em->getRepository('VidalDrugBundle:Publication')->findOneById($id);

        if ((!$publication || $publication->getEnabled() === false) && !$request->query->has('test')) {
            throw $this->createNotFoundException();
        }

        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');
        if ($invisible == false && $publication->getInvisible()) {
            throw $this->createNotFoundException();
        }

        $title = $this->strip($publication->getTitle());
        $text = $publication->getBody();
        $description = $this->stripDescr($publication->getBody(), 185);

        return array(
            'publication' => $publication,
            'text' => $text,
            'menu_left' => 'news',
            'keywords' => '',
            'seotitle' => $title . ' - Новости Ветеринарии - cправочник лекарственных препаратов',
            'ogTitle' => $title,
            'nextPublication' => $em->getRepository('VidalDrugBundle:Publication')->findNextPublication($id, Publication::PROJECT_VETERINARY),
            'prevPublication' => $em->getRepository('VidalDrugBundle:Publication')->findPrevPublication($id, Publication::PROJECT_VETERINARY),
            'description' => $description,
            'menu_veterinar' => 'veterinar',
        );
    }

    /**
     * @Route("/veterinar/novosti", name="v_news", options={"expose"=true})
     * @Template("VidalVeterinarBundle:Vidal:news.html.twig")
     */
    public function newsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $page = $request->query->get('p', null);
        $testMode = $request->query->has('test');
        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');

        if ($page == 1) {
            return $this->redirect($this->generateUrl('news'), 301);
        }
        elseif ($page == null) {
            $page = 1;
        }

        $params = array(
            'title' => 'Новости Ветеринарии',
            'menu_veterinar' => 'veterinar',
        );

        if ($page > 1) {
            $params['extra_title'] = ' - страница ' . $page;
            $params['extra_description'] = ' Страница ' . $page . '.';
        }

        if ($page == 1) {
            $params['publicationsPriority'] = $em->getRepository('VidalDrugBundle:Publication')->findLastPriority($testMode, Publication::PROJECT_VETERINARY);
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $em->getRepository('VidalDrugBundle:Publication')->getQueryEnabled($testMode, $invisible, Publication::PROJECT_VETERINARY),
            $page,
            self::PUBLICATIONS_PER_PAGE
        );
        $pagination->setTemplate('VidalMainBundle:News:news_pagination.html.twig');

        $params['publicationsPagination'] = $pagination;
        $params['keywords'] = '';
        $params['page'] = $page;

        return $params;
    }

    private function stripDescr($string, $maxLength = null)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace('/(®|™)/iu', '', $string);
        $string = str_replace('&nbsp;', ' ', $string);
        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = mb_substr($string, 0, 175, 'utf-8');

        return $string;
    }

    /**
     * @Route("/veterinar", name="veterinar")
     * @Template("VidalVeterinarBundle:Vidal:veterinar.html.twig")
     */
    public function veterinarAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('veterinar');
        /** @var EntityManager $emDrug */
        $emDrug = $this->getDoctrine()->getManager('drug');

        $t = $request->query->get('t', 'all'); // тип препараты=p / производители=c / представительства=r / вещества=m / везде=all
        $p = $request->query->get('p', 1); // номер страницы
        $l = $request->query->get('l', null); // буква
        $q = $request->query->get('q', null); // текстовый запрос
        $o = $request->query->get('o', ''); # поисковый запрос до транслитерации
        $o = trim($o);

        if ($l) {
            $letters = array_flip($this->letters);

            if (isset($letters[$l])) {
                return $this->redirect($this->generateUrl('veterinar_letter', array(
                    'letter' => $letters[$l]
                )), 301);
            }
        }

        $params = array(
            't' => $t,
            'p' => $p,
            'q' => $q,
            'o' => $o,
            'l' => $l,
            'title' => 'Видаль-Ветеринар',
            'menu_veterinar' => 'veterinar',
            'vetpage' => true,
            'vetIndexPage' => true,
        );

        $params['publications'] = $emDrug->getRepository("VidalDrugBundle:Publication")->findLast(3, false, false, Publication::PROJECT_VETERINARY);

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        if (empty($q)) {
            $products = $em->getRepository('VidalVeterinarBundle:Product')->findByName();
            $pagination = $this->get('knp_paginator')->paginate($products, $p, self::PRODUCTS_PER_PAGE);
            $params['pagination'] = $pagination;
            $params['products'] = $pagination;
            $params['urlCanonical'] = 'https://www.vidal.ru/veterinar';

            if ($pagination->getTotalItemCount()) {
                $productIds = $this->getProductIds($pagination);
                $params['productCompanies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByProducts($productIds);
                $params['productPictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findByProductIds($productIds);
            }

            return $params;
        }

        # поисковый запрос не может быть меньше 2
        if (mb_strlen($q, 'UTF-8') < 2) {
            return $this->render('VidalVeterinarBundle:Vidal:search_too_short.html.twig', $params);
        }

        if ($t == 'all' || $t == 'p') {
            $products = $em->getRepository('VidalVeterinarBundle:Product')->findByQuery($q);
            $pagination = $this->get('knp_paginator')->paginate($products, $p, self::PRODUCTS_PER_PAGE);
            $params['products'] = $pagination;

            if ($pagination->getTotalItemCount()) {
                $productIds = $this->getProductIds($pagination);
                $params['productCompanies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByProducts($productIds);
                $params['productPictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findByProductIds($productIds);
            }
        }

        if ($p == 1) {
            # поиск по активному веществу
            if ($t == 'all' || $t == 'm') {
                $params['molecules'] = $em->getRepository('VidalVeterinarBundle:Molecule')->findByQuery($q);
            }

            # производители
            if ($t == 'all' || $t == 'c') {
                $params['companies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByQuery($q);
            }

            # представительства
            if ($t == 'all' || $t == 'r') {
                $params['infoPages'] = $em->getRepository('VidalVeterinarBundle:InfoPage')->findByQuery($q);
            }
        }

        if (empty($params['molecules'])
            && empty($params['companies'])
            && empty($params['infoPages'])
            && (empty($params['products']) || $params['products']->getTotalItemCount() == 0)
        ) {
            if (preg_match("/[a-z\\[\\]\\;\\'\\,\\.\\ ]+/", $q)) {
                return $this->redirect($this->generateUrl('veterinar', array(
                    'q' => $this->modifySearchQuery($q),
                    'o' => $q,
                    't' => $t,
                )));
            }
            $params['noResults'] = true;
        }

        $params['keywords'] = $this->keywords($params['title']);

        return $params;
    }

    /**
     * Клинико-фармакологический указатель ветеринарной базы
     *
     * @Route("/veterinar/kfu", name = "v_kfu")
     * @Template("VidalVeterinarBundle:Vidal:kfu.html.twig")
     */
    public function kfuAction()
    {
        $params = array(
            'title' => 'Клинико-фармакологические указатели | Видаль-Ветеринар',
            'menu_veterinar' => 'kfu',
            'vetpage' => true,
        );

        $params['keywords'] = 'клинико, фармакологические, указатели, кфу';

        return $params;
    }

    /**
     * Клинико-фармакологический указатель ветеринарной базы
     *
     * @Route("/veterinar/kfu/{url}.{ext}", name="v_kfu_item", defaults={"ext"="htm"}, options={"expose":true})
     * @Template("VidalVeterinarBundle:Vidal:kfu_item.html.twig")
     */
    public function kfuItemAction($url)
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $kfu = $em->getRepository('VidalVeterinarBundle:ClinicoPhPointers')->findOneByUrl($url);

        if (!$kfu) {
            throw $this->createNotFoundException();
        }

        $documentIds = $this->getDocumentIds($kfu->getDocuments());
        $params = array(
            'title' => $kfu->getName() . ' | Клинико-фармакологические указатели | Видаль-Ветеринар',
            'menu_veterinar' => 'kfu',
            'kfu' => $kfu,
            'vetpage' => true,
        );

        if (!empty($documentIds)) {
            $products = $em->getRepository('VidalVeterinarBundle:Product')->findByDocumentIds($documentIds);
            $params['products'] = $products;

            if (!empty($products)) {
                $productIds = $this->getProductIds($products);
                $params['companies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByProducts($productIds);
                $params['pictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findByProductIds($productIds);
            }
        }

        $params['keywords'] = $this->keywords('кфу ' . $kfu->getName());

        return $params;
    }

    /**
     * Список препаратов по компании
     *
     * @Route("/veterinar/proizvoditeli", name="proizvoditeli")
     * @Template("VidalVeterinarBundle:Vidal:proizvoditeli.html.twig")
     */
    public function proizvoditeliAction()
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $companies = $em->getRepository('VidalVeterinarBundle:Company')->findAllOrdered();

        return array(
            'title' => 'Фирмы-производители | Видаль-Ветеринар',
            'menu_veterinar' => 'company',
            'companies' => $companies,
            'keywords' => 'видаль, ветеринар, фирмы, производители',
            'vetpage' => true,
        );
    }

    /**
     * Список препаратов по компании
     * @Route("/veterinar/proizvoditeli/{Name}.{ext}", name="v_company", defaults={"ext"="htm"}, requirements={"Name"=".+"})
     *
     * @Template("VidalVeterinarBundle:Vidal:company.html.twig")
     */
    public function companyAction($Name)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('veterinar');
        /** @var Company $company */
        $company = $em->getRepository('VidalVeterinarBundle:Company')->findOneByName($Name);

        if (!$company) {
            $Name = str_replace('"', '', $Name);
            $Name = str_replace("'", '', $Name);

            if ($company = $em->getRepository('VidalVeterinarBundle:Company')->findOneByName($Name)) {
                return $this->redirect($this->generateUrl('v_company', array(
                    'Name' => $Name
                )), 301);
            }
            else {
                throw $this->createNotFoundException();
            }
        }

        if (!$company['countProducts']) {
            throw $this->createNotFoundException();
        }

        $CompanyID = $company['CompanyID'];
        $products = $em->getRepository('VidalVeterinarBundle:Product')->findByCompany($CompanyID);
        $params = array(
            'title' => $this->strip($company['CompanyName']) . ' | Фирмы-производители | Видаль-Ветеринар',
            'menu_veterinar' => 'company',
            'company' => $company,
            'products' => $products,
            'vetpage' => true,
        );

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['companies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByProducts($productIds);
            $params['pictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findByProductIds($productIds);
        }

        $params['keywords'] = 'лекарственные, препараты, ' .
            $this->keywords($this->strip($company['CompanyName']) . ' ' . $company['Country'])
            . ', фирмы, производители';

        return $params;
    }

    /**
     * Страничка представительства и список препаратов
     *
     * @Route("/veterinar/predstavitelstvo", name="predstavitelstvo")
     * @Template("VidalVeterinarBundle:Vidal:predstavitelstvo.html.twig")
     */
    public function predstavitelstvaAction()
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $infoPages = $em->getRepository('VidalVeterinarBundle:InfoPage')->findAllOrdered();

        return array(
            'title' => 'Информационные страницы | Видаль-Ветеринар',
            'menu_veterinar' => 'infoPage',
            'infoPages' => $infoPages,
            'keywords' => 'видаль, ветеринар, представительства, фирм',
            'vetpage' => true,
        );
    }

    /**
     * Страничка представительства и список препаратов
     *
     * @Route("/veterinar/predstavitelstvo/{Name}.{ext}", name="v_inf", defaults={"ext"="htm"})
     * @Template("VidalVeterinarBundle:Vidal:inf.html.twig")
     */
    public function infAction($Name)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('veterinar');
        /** @var InfoPage $infoPage */
        $infoPage = $em->getRepository('VidalVeterinarBundle:InfoPage')->findOneByName($Name);

        if (!$infoPage || !$infoPage['countProducts']) {
            throw $this->createNotFoundException();
        }

        $InfoPageID = $infoPage['InfoPageID'];

        $picture = $em->getRepository('VidalVeterinarBundle:Picture')->findByInfoPageID($InfoPageID);
        $params = array(
            'title' => $this->strip($infoPage['RusName']) . ' | Информационные страницы | Видаль-Ветеринар',
            'menu_veterinar' => 'infoPage',
            'infoPage' => $infoPage,
            'picture' => $picture,
            'portfolios' => $em->getRepository('VidalVeterinarBundle:InfoPage')->findPortfolios($InfoPageID),
            'vetpage' => true,
        );
        $documentIds = $em->getRepository('VidalVeterinarBundle:Document')->findIdsByInfoPageID($InfoPageID);

        if (!empty($documentIds)) {
            $products = $em->getRepository('VidalVeterinarBundle:Product')->findByDocumentIDs($documentIds);

            if (!empty($products)) {
                $productIds = $this->getProductIds($products);
                $params['products'] = $products;
                $params['companies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByProducts($productIds);
                $params['pictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findByProductIds($productIds);
            }
        }

        $params['keywords'] = 'лекарственные, препараты, ' .
            $this->keywords($this->strip($infoPage['RusName']) . ' ' . $infoPage['Country'])
            . ', представительства, фирм';

        return $params;
    }

    /**
     * Описание по документу и отображение информации по препаратам или веществу
     * @Route("/veterinar/opisanie/{name}.{ext}", name="v_document", requirements={"DocumentID":"\d+"}, defaults={"ext"="htm"})
     *
     * @Template("VidalVeterinarBundle:Vidal:document.html.twig")
     */
    public function documentAction($name)
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $params = array();
        $DocumentID = intval(substr($name, strrpos($name, '_') + 1));
        $product = $em->getRepository('VidalVeterinarBundle:Product')->findOneByDocumentID($DocumentID);

        if (!$product) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->generateUrl('v_product', array(
            'ProductID' => $product['ProductID'],
            'EngName' => $product['Name'],
        )));
    }

    /**
     * Список препаратов по активному веществу: одно-монокомпонентные
     * @Route("/veterinar/molecule/{url}", name="v_molecule", requirements={"url"=".+"})
     * @Template("VidalVeterinarBundle:Vidal:molecule.html.twig")
     */
    public function moleculeAction($url)
    {
        $em = $this->getDoctrine()->getManager('veterinar');

        if (preg_match('/^\d+$/', $url)) {
            $molecule = $em->getRepository('VidalVeterinarBundle:Molecule')->findByMoleculeID($url);

            if (!$molecule) {
                throw $this->createNotFoundException();
            }

            return $this->redirect($this->generateUrl('v_molecule', array('url' => $molecule->getUrl())), 301);
        }

        $molecule = $em->getRepository('VidalVeterinarBundle:Molecule')->findByMoleculeUrl($url);

        if (!$molecule) {
            throw $this->createNotFoundException();
        }

        $MoleculeID = $molecule->getMoleculeID();

        $document = $em->getRepository('VidalVeterinarBundle:Document')->findByMoleculeID($MoleculeID);
        $params = array(
            'molecule' => $molecule,
            'document' => $document,
            'title' => mb_strtoupper($this->strip($molecule->getTitle()), 'UTF-8') . ' | Активные вещества',
            'vetpage' => true,
        );

        $params['description'] = $this->mb_ucfirst($this->strip($molecule->getRusName()))
            . ' ('
            . $this->strip($molecule->getLatName())
            . ') - активное вещество. Список препаратов, показания, противопоказания, порядок применения, побочные эффекты.';

        $params['keywords'] = $this->keywords($molecule->getRusName())
            . ', ' . $this->keywords($molecule->getLatName())
            . ', активное, вещество, побочные, эффекты, показания, применения, препарат';

        return $params;
    }

    /**
     * Отображение списка препаратов, в состав которых входит активное вещество (Molecule)
     *
     * @Route("/veterinar/molecule-in/{url}", name="v_molecule_included", requirements={"MoleculeID":"\d+"})
     * @Template("VidalVeterinarBundle:Vidal:molecule_included.html.twig")
     */
    public function moleculeIncludedAction($url)
    {
        $em = $this->getDoctrine()->getManager('veterinar');

        if (preg_match('/^\d+$/', $url)) {
            $molecule = $em->getRepository('VidalVeterinarBundle:Molecule')->findByMoleculeID($url);

            if (!$molecule) {
                throw $this->createNotFoundException();
            }

            return $this->redirect($this->generateUrl('v_molecule_included', array('url' => $molecule->getUrl())), 301);
        }

        $molecule = $em->getRepository('VidalVeterinarBundle:Molecule')->findByMoleculeUrl($url);

        if (!$molecule) {
            throw $this->createNotFoundException();
        }

        $MoleculeID = $molecule->getMoleculeID();

        $params = array(
            'molecule' => $molecule,
            'vetpage' => true,
            'title' => mb_strtoupper($this->strip($molecule->getTitle()), 'utf-8') . ' | Активные вещества в препаратах',
        );
        $products = $em->getRepository('VidalVeterinarBundle:Product')->findByMoleculeID($MoleculeID);

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['products'] = $products;
            $params['companies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByProducts($productIds);
            $params['pictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findByProductIds($productIds);
        }

        $params['keywords'] = $this->keywords($molecule->getRusName())
            . ', ' . $this->keywords($molecule->getLatName())
            . ', активное, вещество, ветеринар, список, препарат';

        return $params;
    }

    /**
     * Страничка рассшифровки МНН аббревиатур
     *
     * @Route("/veterinar/gnp", name="v_gnp")
     * @Template("VidalVeterinarBundle:Vidal:gnp.html.twig")
     */
    public function gnpAction()
    {
        $em = $this->getDoctrine()->getManager('veterinar');

        $params = array(
            'title' => 'Международные наименования - МНН',
            'gnps' => $em->getRepository('VidalVeterinarBundle:MoleculeBase')->findAll(),
            'vetpage' => true,
        );

        return $params;
    }

    /**
     * @Route("/veterinar/{EngName}~{ProductID}.{ext}", name="v_product_old", requirements={"ProductID":"\d+", "EngName"=".+"}, defaults={"ext"="htm"})
     */
    public function productRedirectAction($EngName, $ProductID)
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $product = $em->getRepository('VidalVeterinarBundle:Product')->findByProductID($ProductID);

        if (empty($product) || $product['inactive'] === true) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->generateUrl('v_product', array(
            'EngName' => $product['Name'],
            'ProductID' => $product['ProductID']
        )), 301);
    }

    /**
     * Описание препарата
     * @Route("/veterinar/{EngName}-{ProductID}.{ext}", name="v_product", requirements={"ProductID":"\d+", "EngName"=".*"}, defaults={"ext"="htm"})*
     * @Template("VidalVeterinarBundle:Vidal:document.html.twig")
     */
    public function productAction($EngName, $ProductID)
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $params = array(
            'vetpage' => true,
        );

        /** @var Product $product */
        $product = $em->getRepository('VidalVeterinarBundle:Product')->findByProductID($ProductID);

        if (empty($product) || $product['inactive'] === true) {
            throw $this->createNotFoundException();
        }

        if ($product['Name'] != $EngName) {
            return $this->redirect($this->generateUrl('v_product', array(
                'EngName' => $product['Name'],
                'ProductID' => $product['ProductID']
            )), 301);
        }

        $productEntity = $em->getRepository('VidalVeterinarBundle:Product')->findEntityByProductID($ProductID);
        $params['productEntity'] = $productEntity;

        $params['seotitle'] = $this->strip($product['RusName'])
            . ' инструкция по применению, состав, показания, противопоказания, побочные эффекты – '
            . $product['ZipInfo']
            . ' - справочник препаратов и лекарств';
        $document = $em->getRepository('VidalVeterinarBundle:Document')->findByProductID($ProductID);

        if ($document) {
            $articleId = $document->getArticleID();
            $params['document'] = $document;
            $params['articleId'] = $articleId;
            $params['infoPages'] = $em->getRepository('VidalVeterinarBundle:InfoPage')->findByDocumentID($document->getDocumentID());
        }

        $productIds = array($product['ProductID']);
        $params['product'] = $product;
        $params['products'] = array($product);
        $params['owners'] = $em->getRepository('VidalVeterinarBundle:Company')->findOwnersByProducts($productIds);
        $params['distributors'] = $em->getRepository('VidalVeterinarBundle:Company')->findDistributorsByProducts($productIds);
        $params['pictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findAllByProductIds($productIds);
        $params['molecules'] = $em->getRepository('VidalVeterinarBundle:Molecule')->findByProductID($ProductID);

        $altTitle = preg_replace('/<sup\b[^>]*>(.*?)<\/sup>/i', '', $product['RusName2']);
        $altTitle = mb_strtolower($altTitle, 'utf-8') . ' инструкция по применению';
        $altTitle = $this->mb_ucfirst($altTitle);
        $params['img_alt_title'] = $altTitle;

        $params['keywords'] = '';

        # description
        $params['description'] = $this->mb_ucfirst($product['RusName2']) . ', ';
        $zipParts = explode(':', $product['ZipInfo']);
        $params['description'] .= $zipParts[0] . '. ';
        $params['description'] .= 'Показания, противопоказания, порядок применения, побочные эффекты.';

        if (!empty($params['owners'])) {
            $params['description'] .= ' ' . $params['owners'][0]['LocalName'];
        }

        foreach ($params['distributors'] as $d) {
            $params['description'] .= ', ' . $d['CompanyRusNote'] . ' ' . $d['LocalName'];
        }

        return $params;
    }

    /**
     * Функция отображения веществ по букве
     * @Route("/veterinar/molecules/bukva/{letter}", name="molecules_letter")
     * @Template("VidalVeterinarBundle:Vidal:molecules_letter.html.twig")
     */
    public function moleculesLetterAction(Request $request, $letter)
    {
        if (!isset($this->molecule_letters[$letter])) {
            throw $this->createNotFoundException();
        }

        $l = $this->molecule_letters[$letter];

        $em = $this->getDoctrine()->getManager('veterinar');
        $p = $request->query->get('p', null); // номер страницы

        if ($p == 1) {
            return $this->redirect($this->generateUrl('molecules_letter', array('letter' => $letter)), 301);
        }
        elseif ($p == null) {
            $p = 1;
        }

        $query = $em->getRepository('VidalVeterinarBundle:Molecule')->getQueryByLetter($l);

        $params = array(
            'letter' => $letter,
            'l' => $l,
            'title' => 'Активные вещества на букву ' . $l,
            'menu_veterinar' => 'veterinar',
            'vetpage' => true,
            'pagination' => $this->get('knp_paginator')->paginate($query, $p, 50),
        );

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        $params['seotitle'] = 'Активые вещества на букву ' . $l;
        $params['keywords'] = '';

        $params['description'] = 'Видаль - Ветеринар. Справочник лекарственных препаратов, содержащий 
        подробные описания и инструкции по применению лекарственных средств. Так же, на сайте: 
        медицинская энциклопедия с описанием болезней, новости медицины и фармацевтики, 
        взаимодействие лекарств и исследования их эффективности на букву ' . $l;

        return $params;
    }

    /**
     * Функция отображения препаратов по букве
     * @Route("/veterinar/bukva/{letter}", name="veterinar_letter")
     * @Template("VidalVeterinarBundle:Vidal:veterinar_letter.html.twig")
     */
    public function veterinarLetterAction(Request $request, $letter)
    {
        if (!isset($this->letters[$letter])) {
            throw $this->createNotFoundException();
        }

        $l = $this->letters[$letter];

        $em = $this->getDoctrine()->getManager('veterinar');
        $p = $request->query->get('p', null); // номер страницы

        if ($p == 1) {
            return $this->redirect($this->generateUrl('veterinar_letter', array('letter' => $letter)), 301);
        }
        elseif ($p == null) {
            $p = 1;
        }

        $params = array(
            'letter' => $letter,
            'l' => $l,
            'title' => 'Видаль - Ветеринар',
            'menu_veterinar' => 'veterinar',
            'vetpage' => true,
        );

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        $products = $em->getRepository('VidalVeterinarBundle:Product')->findByLetter($l);
        $pagination = $this->get('knp_paginator')->paginate($products, $p, self::PRODUCTS_PER_PAGE);
        $params['products'] = $pagination;

        if ($pagination->getTotalItemCount()) {
            $productIds = $this->getProductIds($pagination);
            $params['companies'] = $em->getRepository('VidalVeterinarBundle:Company')->findByProducts($productIds);
            $params['pictures'] = $em->getRepository('VidalVeterinarBundle:Picture')->findByProductIds($productIds);
        }

        $params['seotitle'] = 'Ветеринарные препараты на букву ' . $l;
        if ($p > 1) {
            $params['seotitle'] .= ' - страница ' . $p;
        }

        $params['keywords'] = '';

        $params['description'] = 'Видаль - Ветеринар. Справочник лекарственных препаратов, содержащий 
        подробные описания и инструкции по применению лекарственных средств. Так же, на сайте: 
        медицинская энциклопедия с описанием болезней, новости медицины и фармацевтики, 
        взаимодействие лекарств и исследования их эффективности на букву ' . $l;

        if ($p > 1) {
            $params['description'] .= ' страница ' . $p;
        }

        return $params;
    }

    /**
     * @Route("/veterinar/molecules", name="v_molecules")
     * @Template("VidalVeterinarBundle:Vidal:molecules.html.twig")
     */
    public function moleculesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $q = $request->query->get('q', null);
        $l = $request->query->get('l', null);
        $p = $request->query->get('p', 1);

        if ($l) {
            $query = $em->getRepository('VidalVeterinarBundle:Molecule')->getQueryByLetter($l);
        }
        elseif ($q) {
            $query = $em->getRepository('VidalVeterinarBundle:Molecule')->getQueryByString($q);
        }
        else {
            $query = $em->getRepository('VidalVeterinarBundle:Molecule')->getQuery();
        }

        $params = array(
            'menu_drugs' => 'molecule',
            'title' => 'Активные вещества',
            'q' => $q,
            'l' => $l,
            'pagination' => $this->get('knp_paginator')->paginate($query, $p, 50),
            'vetpage' => true,
        );

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        return $params;
    }

    /**
     * Функция генерации дерева с кодами КФУ
     *
     * @Route("/veterinar/kfu-generator", name="v_kfu_generator")
     * @Template("VidalVeterinarBundle:Vidal:kfu_generator.html.twig")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function kfuGeneratorAction()
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'kfu.json';
        $json = json_decode(file_get_contents($file), true);

        return array('codes' => $json);
    }

    /**
     * @Route("/veterinar/podrobno-o-preparate", name="veterinar_portfolios")
     * @Template("VidalVeterinarBundle:Vidal:portfolios.html.twig")
     */
    public function portfoliosAction()
    {
        throw $this->createNotFoundException();

        $em = $this->getDoctrine()->getManager('veterinar');
        $portfolios = $em->getRepository('VidalVeterinarBundle:PharmPortfolio')->findActive();

        $params = array(
            'title' => 'Портфели препаратов | Видаль - Ветеринар',
            'menu_veterinar' => 'portfolios',
            'portfolios' => $portfolios,
            'vetpage' => true,
        );

        $params['keywords'] = 'видаль, ветеринар, портфели, препаратов';

        return $params;
    }

    /**
     * [AJAX] Подгрузка дерева КФУ
     *
     * @Route("/veterinar/kfu-ajax", name="v_kfu_ajax", options={"expose":true})
     */
    public function kfuAjaxAction(Request $request)
    {
        if ($request->request->has('root')) {
            $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'kfu.json';
            $json = json_decode(file_get_contents($file), true);

            $root = $request->request->get('root');
            $data = $json[$root]['children'];

            return new JsonResponse($data);
        }

        return new JsonResponse();
    }

    /**
     * @Route("/veterinar/podrobno-o-preparate/{url}", name="veterinar_portfolio")
     * @Template("VidalVeterinarBundle:Vidal:portfolio.html.twig")
     */
    public function portfolioAction($url)
    {
        $em = $this->getDoctrine()->getManager('veterinar');
        $portfolio = $em->getRepository('VidalVeterinarBundle:PharmPortfolio')->findOneByUrl($url);

        if (!$portfolio) {
            throw $this->createNotFoundException();
        }

        $params = array(
            'title' => $this->strip($portfolio->getTitle()) . ' | Портфель препарата | Видаль - Ветеринар',
            'menu_veterinar' => 'portfolios',
            'portfolio' => $portfolio,
            'products' => $em->getRepository('VidalVeterinarBundle:Product')->findByPortfolio($portfolio->getId()),
            'vetpage' => true,
        );

        $params['keywords'] = 'видаль, ветеринар, препарат, ' . $this->keywords($portfolio->getTitle());

        return $params;
    }

    /** Получить массив идентификаторов продуктов */
    private function getProductIds($products)
    {
        if (empty($products)) {
            return array();
        }

        $productIds = array();

        if ($products[0] instanceof Product) {
            foreach ($products as $product) {
                $productIds[] = $product->getProductID();
            }
        }
        else {
            foreach ($products as $product) {
                $productIds[] = $product['ProductID'];
            }
        }

        return $productIds;
    }

    private function getDocumentIds($documents)
    {
        $ids = array();

        foreach ($documents as $document) {
            $ids[] = $document->getDocumentID();
        }

        return $ids;
    }

    private function strip($string)
    {
        return strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));
    }

    private function modifySearchQuery($query, $eng2rus = true)
    {
        $eng = explode(' ', "q w e r t y u i o p [ ] a s d f g h j k l ; ' z x c v b n m , .");
        $rus = explode(' ', 'й ц у к е н г ш щ з х ъ ф ы в а п р о л д ж э я ч с м и т ь б ю');

        return $eng2rus ? str_replace($eng, $rus, $query) : str_replace($rus, $eng, $query);
    }

    private function mb_ucfirst($str, $enc = 'utf-8')
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc) . mb_strtolower(mb_substr($str, 1, mb_strlen($str, $enc), $enc), $enc);
    }

    private function keywords($str)
    {
        $str = str_replace(['%', ',', '<SUP>&reg;</SUP>'], '', $str);
        $str = str_replace(['+', '/', '-', '.'], ' ', $str);
        $str = mb_strtolower(trim($str), 'utf-8');
        $exploded = explode(' ', $str);
        $words = array();

        foreach ($exploded as $word) {
            if (strpos($word, '-') !== false) {
                $exp = explode('-', $word);
                foreach ($exp as $e) {
                    $words[] = $e;
                }
            }
            else {
                $words[] = $word;
            }
        }

        return implode(', ', $words);
    }
}
