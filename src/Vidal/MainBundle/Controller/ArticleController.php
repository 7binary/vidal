<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Vidal\DrugBundle\Entity\Art;
use Vidal\DrugBundle\Entity\Article;
use Vidal\DrugBundle\Entity\ArticleCategory;
use Vidal\DrugBundle\Entity\ArticleRubrique;

class ArticleController extends Controller
{
    const ARTICLES_PER_PAGE = 14;
    const PHARM_PER_PAGE = 5;

    /**
     * @Route("/vracham/expert", name="vracham_expert")
     */
    public function vrachamExpertAction()
    {
        return $this->redirect($this->generateUrl('vracham_e_vidal'), 301);
    }

    /**
     * @Route("/vracham/expert/vidal-cd", name="vracham_expert_cd")
     * @Route("/vracham/Vidal-CD")
     */
    public function vrachamExpertCdAction()
    {
        return $this->redirect($this->generateUrl('vracham_e_vidal'), 301);
    }

    /**
     * @Route("/vracham/vidal-cd", name="vracham_cd")
     */
    public function vrachamCdAction()
    {
        return $this->redirect($this->generateUrl('vracham_e_vidal'), 301);
    }

    /**
     * @Route("/vracham/e-vidal", name="vracham_e_vidal")
     * @Template("VidalMainBundle:Article:vrachamExpertCd.html.twig")
     */
    public function vrachamEVidalAction()
    {
        return array(
            'seotitle' => 'Справочник Видаль скачать – справочник Видаль',
            'description' => 'Скачать справочник Видаль Лекарственные препараты в России. Информация, представленная в справочниках, соответствует печатным изданиям соответствующих лет',
            'menu' => 'vracham',
            'title' => 'Скачать электронные справочники Видаль',
            'hideMobile' => true,
            'keywords' => '',
        );
    }

    /**
     * Конкретная статья рубрики c подразделом энциклопедии
     * @Route("/encyclopedia/{rubrique}/{category}/{link}", name="article_with_category")
     * @Template("VidalMainBundle:Article:article.html.twig")
     */
    public function articleWithCategoryAction(Request $request, $rubrique, $category, $link)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $rubriqueEntity = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findOneByRubrique($rubrique);
        $categoryEntity = $em->getRepository('VidalDrugBundle:ArticleCategory')->findOneByUrl($category);

        if (!$rubriqueEntity || !$categoryEntity) {
            throw $this->createNotFoundException();
        }

        /** @var Article $article */
        $article = $em->getRepository('VidalDrugBundle:Article')->findOneByCategoryLink($categoryEntity->getId(), $link);

        if (!$article) {
            throw $this->createNotFoundException();
        }

        $testMode = $request->query->has('test');

        if (in_array($article->getId(), array(475, 455, 454, 476, 674, 399))) {
            return $this->redirect($this->generateUrl('article', array(
                'rubrique' => 'esteticheskaya-medicina',
                'link' => $article->getLink(),
            )), 301);
        }

        if ($article == null) {
            $article = $em->getRepository('VidalDrugBundle:Article')->findOneByLink(str_replace('_', '-', $link));
        }

        if (!$testMode && (!$rubriqueEntity || !$rubriqueEntity->getEnabled() || !$article || $article->getEnabled() === false)) {
            throw $this->createNotFoundException();
        }

        if ($rubriqueEntity->getRubrique() !== $rubrique || $article->getLink() !== $link) {
            return $this->redirect($this->generateUrl('article', array(
                'rubrique' => $rubriqueEntity->getRubrique(),
                'link' => $article->getLink(),
            )), 301);
        }

        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');
        if ($invisible == false && $article->getInvisible()) {
            throw $this->createNotFoundException();
        }

        $title = $this->strip($article->getTitle());

        $params = array(
            'seotitle' => $title,
            'keywords' => '',
            'ogTitle' => $title,
            'menu' => 'articles',
            'rubrique' => $rubriqueEntity,
            'category' => $categoryEntity,
            'article' => $article,
            'description' => $this->strip($article->getAnnounce()),
            'hideMobile' => true,
        );

        $articleId = $article->getId();
        $isDoctor = $this->get('security.context')->isGranted('ROLE_DOCTOR');
        $products = $em->getRepository('VidalDrugBundle:Product')->findByArticle($articleId, $isDoctor);

        if (!empty($products)) {
            # если нашлись препараты по статье - надо разбить на 2 группы: безрецептурные и рецептурные
            if ($isDoctor) {
                $productsPre = array();
                $productsNon = array();

                foreach ($products as $product) {
                    $product['NonPrescriptionDrug'] ? $productsNon[] = $product : $productsPre[] = $product;
                }

                if (!empty($productsNon)) {
                    $productIds = $this->getProductIds($productsNon);
                    $params['products'] = $productsNon;
                    $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
                    $params['pictures'] = $em->getRepository('VidalDrugBundle:Picture')->findByProductIds($productIds, date('Y'));
                    $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($productsNon);
                }

                if (!empty($productsPre)) {
                    $productIds = $this->getProductIds($productsPre);
                    $params['pre_products'] = $productsPre;
                    $params['pre_companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
                    $params['pre_pictures'] = $em->getRepository('VidalDrugBundle:Picture')->findByProductIds($productIds, date('Y'));
                    $params['pre_infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($productsPre);
                }

                $params['molecules'] = $em->getRepository('VidalDrugBundle:Molecule')->findByArticle($articleId);
            }
            else {
                $productIds = $this->getProductIds($products);
                $params['products'] = $products;
                $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
                $params['pictures'] = $em->getRepository('VidalDrugBundle:Picture')->findByProductIds($productIds, date('Y'));
                $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
            }
        }
        if ($link == "doctor_chistoteloff") {
            $params['products'] = array();
            $params['pre_products'] = array();
        }
        if ($link == "doktor-chistoteloff") {
            $params['products'] = array();
            $params['pre_products'] = array();
        }

        # перелинковка с препаратами
        $text = $article->getBody();

        if ($article->getDisableLinks() == false) {
            $bodyLinked = $article->getBodyLinked();
            $text = empty($bodyLinked) ? $text : $bodyLinked;
        }

        $params['text'] = $text;

        return $params;
    }

    /**
     * Конкретная статья рубрики энциклопедии
     * @Route("/encyclopedia/{rubrique}/{link}", name="article")
     * @Template("VidalMainBundle:Article:article.html.twig")
     */
    public function articleAction(Request $request, $rubrique, $link)
    {
        # редиректы разделов
        if ($rubrique == 'sredstva-gigieny' || $rubrique == 'sredstva-dlya-okazaniya-pervoy-pomoshchi') {
            return $this->redirect($this->generateUrl('article', array(
                'rubrique' => 'medicinskie-izdeliya',
                'link' => $link,
            )), 301);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        /** @var ArticleRubrique $rubriqueEntity */
        $rubriqueEntity = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findOneByRubrique($rubrique);

        if (!$rubriqueEntity) {
            throw $this->createNotFoundException();
        }

        /** @var Article $article */
        $article = $em->getRepository('VidalDrugBundle:Article')->findOneByRubriqueLink($rubriqueEntity->getId(), $link);
        /** @var ArticleCategory $category */
        $category = $em->getRepository('VidalDrugBundle:ArticleCategory')->findByRubriqueLink($rubrique, $link);
        $testMode = $request->query->has('test');

        if ($category) {
            $title = $this->strip($category->getTitle());

            $params = array(
                'seotitle' => $title,
                'keywords' => '',
                'ogTitle' => $title,
                'menu' => 'articles',
                'rubrique' => $rubriqueEntity,
                'category' => $category,
                'description' => $this->strip($category->getAnnounce()),
                'hideMobile' => true,
                'articles' => $category->getArticles(),
            );

            return $this->render('VidalMainBundle:Article:rubrique_category.html.twig', $params);
        }

        if (!$article) {
            throw $this->createNotFoundException();
        }

        if (in_array($article->getId(), array(475, 455, 454, 476, 674, 399))) {
            return $this->redirect($this->generateUrl('article', array(
                'rubrique' => 'esteticheskaya-medicina',
                'link' => $article->getLink(),
            )), 301);
        }

        if ($article == null) {
            $article = $em->getRepository('VidalDrugBundle:Article')->findOneByLink(str_replace('_', '-', $link));
        }

        if (!$testMode && (!$rubriqueEntity || !$rubriqueEntity->getEnabled() || !$article || $article->getEnabled() === false)) {
            throw $this->createNotFoundException();
        }

        if ($rubriqueEntity->getRubrique() !== $rubrique || $article->getLink() !== $link) {
            return $this->redirect($this->generateUrl('article', array(
                'rubrique' => $rubriqueEntity->getRubrique(),
                'link' => $article->getLink(),
            )), 301);
        }

        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');
        if ($invisible == false && $article->getInvisible()) {
            throw $this->createNotFoundException();
        }

        $title = $this->strip($article->getTitle());

        $params = array(
            'seotitle' => $title,
            'keywords' => '',
            'ogTitle' => $title,
            'menu' => 'articles',
            'rubrique' => $rubriqueEntity,
            'article' => $article,
            'description' => $this->strip($article->getAnnounce()),
            'nextArticle' => $em->getRepository('VidalDrugBundle:Article')->findNextArticle($article),
            'prevArticle' => $em->getRepository('VidalDrugBundle:Article')->findPrevArticle($article),
            'hideMobile' => true,
        );

        $articleId = $article->getId();
        $isDoctor = $this->get('security.context')->isGranted('ROLE_DOCTOR');
        $products = $em->getRepository('VidalDrugBundle:Product')->findByArticle($articleId, $isDoctor);

        if (!empty($products)) {
            # если нашлись препараты по статье - надо разбить на 2 группы: безрецептурные и рецептурные
            if ($isDoctor) {
                $productsPre = array();
                $productsNon = array();

                foreach ($products as $product) {
                    $product['NonPrescriptionDrug'] ? $productsNon[] = $product : $productsPre[] = $product;
                }

                if (!empty($productsNon)) {
                    $productIds = $this->getProductIds($productsNon);
                    $params['products'] = $productsNon;
                    $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
                    $params['pictures'] = $em->getRepository('VidalDrugBundle:Picture')->findByProductIds($productIds, date('Y'));
                    $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($productsNon);
                }

                if (!empty($productsPre)) {
                    $productIds = $this->getProductIds($productsPre);
                    $params['pre_products'] = $productsPre;
                    $params['pre_companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
                    $params['pre_pictures'] = $em->getRepository('VidalDrugBundle:Picture')->findByProductIds($productIds, date('Y'));
                    $params['pre_infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($productsPre);
                }

                $params['molecules'] = $em->getRepository('VidalDrugBundle:Molecule')->findByArticle($articleId);
            }
            else {
                $productIds = $this->getProductIds($products);
                $params['products'] = $products;
                $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
                $params['pictures'] = $em->getRepository('VidalDrugBundle:Picture')->findByProductIds($productIds, date('Y'));
                $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
            }
        }

        $text = $article->getBody();

        # перелинковка с препаратами и активными веществами, если не отключена у статьи
        if ($article->getDisableLinks() == false) {
            $bodyLinked = $article->getBodyLinked();
            $text = empty($bodyLinked) ? $text : $bodyLinked;
        }

        $params['text'] = $text;

        return $params;
    }

    /**
     * Конкретная рубрика
     * @Route("/encyclopedia/{rubrique}", name="rubrique")
     *
     * @Template("VidalMainBundle:Article:rubrique.html.twig")
     */
    public function rubriqueAction(Request $request, $rubrique)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $testMode = $request->query->has('test');
        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');

        # редиректы разделов
        if ($rubrique == 'sredstva-gigieny' || $rubrique == 'sredstva-dlya-okazaniya-pervoy-pomoshchi') {
            return $this->redirect($this->generateUrl('rubrique', array('rubrique' => 'medicinskie-izdeliya')), 301);
        }

        $rubriqueEntity = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findEnabledByRubrique($rubrique);

        if ($rubriqueEntity == null) {
            throw $this->createNotFoundException();
        }

        if ($rubriqueEntity->getRubrique() !== $rubrique) {
            return $this->redirect($this->generateUrl('rubrique', array('rubrique' => $rubriqueEntity->getRubrique())), 301);
        }

        $articles = $em->getRepository('VidalDrugBundle:Article')->ofRubrique($rubriqueEntity, $testMode, $invisible);

        $params = array(
            'seotitle' => $rubriqueEntity->getTitle(),
            'keywords' => '',
            'menu' => 'articles',
            'rubrique' => $rubriqueEntity,
            'articles' => $articles,
            'hideRubrique' => true,
            'hideMobile' => true,
        );

        if ($rubrique == 'medicinskie-izdeliya') {
            $params['menu_left'] = 'meds';
        }

        if ($rubrique == 'esteticheskaya-medicina') {
            $params['menu_left'] = 'esteticheskaya-medicina';
        }

        return $params;
    }

    /**
     * @Route("/patsientam/entsiklopediya/")
     * @Route("/patsientam/entsiklopediya")
     */
    public function r11()
    {
        return $this->redirect($this->generateUrl('articles'), 301);
    }

    /** @Route("/patsientam/entsiklopediya/{url}", requirements={"url"=".+"}) */
    public function r13($url)
    {
        $em = $this->getDoctrine()->getManager('drug');

        if ($pos = strpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        if ($pos = strpos($url, '_')) {
            $id = substr($url, $pos + 1);
            $article = $em->getRepository('VidalDrugBundle:Article')->findOneById($id);
        }
        else {
            $parts = explode('/', $url);
            if (count($parts) > 1) {
                $lastIndex = count($parts) - 1;
                $link = $parts[$lastIndex];
                $article = $em->getRepository('VidalDrugBundle:Article')->findOneByLink($link);
            }
            else {
                $article = null;
            }
        }

        if (!$article) {
            return $this->redirect($this->generateUrl('index'), 301);
        }

        return $this->redirect($this->generateUrl('article', array(
            'rubrique' => $article->getRubrique()->getRubrique(),
            'link' => $article->getLink(),
        ), 301));
    }

    /**
     * @Route("/vracham/Informatsiya-dlya-spetsialistov")
     * @Route("/vracham/Informatsiya-dlya-spetsialistov/")
     */
    public function r4()
    {
        return $this->redirect($this->generateUrl('vracham'), 301);
    }

    /**
     * Рубрики статей видаля
     * @Route("/encyclopedia", name="articles")
     *
     * @Template()
     */
    public function articlesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $testMode = $request->query->has('test');

        return array(
            'title' => 'Энциклопедия',
            'menu' => 'articles',
            'rubriques' => $em->getRepository('VidalDrugBundle:ArticleRubrique')->findEnabled($testMode),
            'hideMobile' => true,
            'testMode' => $testMode,
        );
    }

    /**
     * @Route("/vracham", name="vracham")
     * @Template("VidalMainBundle:Article:vracham.html.twig")
     */
    public function vrachamAction()
    {
        $em = $this->getDoctrine()->getManager('drug');

        return array(
            'seotitle' => 'Статьи для специалистов в области медицины и фармации – справочник Видаль',
            'keywords' => '',
            'description' => 'Полезная информация для специалистов в области медицины и фармации. Алгоритмы лечения и обзоры различных групп лекарственных препаратов. Актуальная информация в справочнике Видаль',
            'menu' => 'vracham',
            'rubriques' => $em->getRepository('VidalDrugBundle:ArtRubrique')->findActive(),
            'arts' => $em->getRepository('VidalDrugBundle:Art')->findForAnons(),
            'hideMobile' => true,
        );
    }

    /**
     * @Route("/vracham/portfeli-preparatov", name="portfolio")
     * @Route("/vracham/podrobno-o-preparate", name="portfolio_old")
     *
     * @Template("VidalMainBundle:Article:portfolio.html.twig")
     */
    public function portfolioAction()
    {
        if ($this->container->get('request')->get('_route') == 'portfolio_old') {
            return $this->redirect($this->generateUrl('portfolio'), 301);
        }

        $em = $this->getDoctrine()->getManager('drug');

        $params = array(
            'seotitle' => 'Портфели препаратов: данные клинических исследований, статьи лекарственных препаратов – справочник Видаль',
            'description' => 'Портфели препаратов. Опыт клинического применения лекарственных препаратов. Актуальная информация в справочнике Видаль',
            'title' => 'Портфели препаратов',
            'portfolios' => $em->getRepository('VidalDrugBundle:PharmPortfolio')->findActive(),
            'hideMobile' => true,
            'keywords' => '',
        );

        return $params;
    }

    /**
     * @Route("/vracham/portfeli-preparatov/{url}", name="portfolio_item")
     * @Route("/vracham/podrobno-o-preparate/{url}", name="portfolio_item_old")
     *
     * @Template("VidalMainBundle:Article:portfolioItem.html.twig")
     */
    public function portfolioItemAction($url)
    {
        if ($this->container->get('request')->get('_route') == 'portfolio_item_old') {
            return $this->redirect($this->generateUrl('portfolio_item', array('url' => $url)), 301);
        }

        if ($response = $this->checkRole()) {
            return $response;
        }

        $em = $this->getDoctrine()->getManager('drug');
        $portfolio = $em->getRepository('VidalDrugBundle:PharmPortfolio')->findOneByUrl($url);

        if (!$portfolio || !$portfolio->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $params = array(
            'title' => $this->strip($portfolio->getTitle()) . ' | Портфель препарата',
            'portfolio' => $portfolio,
            'products' => $em->getRepository('VidalDrugBundle:Product')->findByPortfolio($portfolio),
            'hideMobile' => true,
            'keywords' => '',
        );

        return $params;
    }

    /**
     * @Route("/vracham/Informatsiya-dlya-spetsialistov/{url}", requirements={"url"=".+"})
     */
    public function r5($url)
    {
        //return $this->redirect($this->generateUrl('art', array('url' => $url)), 301);

        $parts = explode('/', $url);
        $rubriqueName = $parts[0];
        $em = $this->getDoctrine()->getManager('drug');
        $rubrique = $em->getRepository('VidalDrugBundle:ArtRubrique')->findOneByUrl($rubriqueName);

        if (!$rubrique) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($this->generateUrl('art', array('url' => $rubriqueName)), 301);
    }

    /** @Route("/vracham/expert/vzaimodeistvie{url}", requirements={"url"=".+"}) */
    public function interactionRedirect()
    {
        return $this->redirect($this->generateUrl('interaction'), 301);
    }

    /**
     * @Route("/vracham/{url}", name="art", requirements={"url"=".+"})
     * @Template("VidalMainBundle:Article:art.html.twig")
     */
    public function artAction(Request $request, $url)
    {
        if ($url == 'endocrinologiya/glucometri' || $url == 'endocrinologiya/insulinoviye-pompi' || $url == 'endocrinologiya/hemoglobin-testing-system') {
            return $this->redirect($this->generateUrl('rubrique', array('rubrique' => 'medicinskie-izdeliya')), 301);
        }

        # проверка длинного деша
        if (strpos($url, '-–-') !== false) {
            return $this->redirect($this->generateUrl('art', array(
                'url' => str_replace('-–-', '-', $url)
            )), 301);
        }

        # проверка нижнего регистра
        $urlLower = strtolower($url);
        if ($urlLower !== $url) {
            return $this->redirect($this->generateUrl('art', array('url' => $urlLower)), 301);
        }

        $parts = explode('/', $url);
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $rubrique = $em->getRepository('VidalDrugBundle:ArtRubrique')->findOneByUrl($parts[0]);

        if ($rubrique === null) {
            throw $this->createNotFoundException();
        }

        $params = array(
            'menu' => 'vracham',
            'rubrique' => $rubrique,
            'hideMobile' => true,
            'keywords' => '',
        );

        # находим, если указана статья по-старому
        $pos = strpos($url, '.');
        if ($pos !== false) {
            $index = count($parts) - 1;
            $link = $parts[$index];
            $pos = strpos($link, '.');
            $link = substr($link, 0, $pos);
            $pos = strpos($link, '_');
            if ($pos !== false) {
                $id = substr($link, $pos + 1);
                $params['article'] = $em->getRepository('VidalDrugBundle:Art')->findOneById($id);
            }
            else {
                $params['article'] = $em->getRepository('VidalDrugBundle:Art')->findOneByLink($link);
            }
            array_pop($parts);
        }

        # прежний редирект
        $pos = strpos($url, '~');

        if ($pos !== false) {
            $id = substr($url, $pos + 1);
            /** @var Art $article */
            $article = $em->getRepository('VidalDrugBundle:Art')->findOneById($id);
            $parts = array();

            if ($rubrique = $article->getRubrique()) {
                $parts[] = $rubrique->getUrl();
            }
            if ($type = $article->getType()) {
                $parts[] = $type->getUrl();
            }
            if ($category = $article->getCategory()) {
                $parts[] = $category->getUrl();
            }

            $parts[] = $article->getLink();

            return $this->redirect($this->generateUrl('art', array('url' => implode('/', $parts))), 301);
        }

        # если последний элемент - название статьи
        $index = count($parts) - 1;
        /** @var Art $article */
        $article = $em->getRepository('VidalDrugBundle:Art')->findOneByRubriqueLink($rubrique->getId(), $parts[$index]);

        if ($article) {
            $params['article'] = $article;
            array_pop($parts);
            # перелинковка с препаратами
            $text = $article->getBody();

            if ($article->getDisableLinks() == false) {
                $bodyLinked = $article->getBodyLinked();
                $text = $article->getBody();
                $text = empty($bodyLinked) ? $text : $bodyLinked;
            }
            $params['text'] = $text;
        }

        $count = count($parts);
        $invisible = $this->get('security.context')->isGranted('ROLE_INVISIBLE');

        if ($count == 1) {
            $params['types'] = $em->getRepository('VidalDrugBundle:ArtType')->findByRubrique($rubrique);
            $query = $em->getRepository('VidalDrugBundle:Art')->getQueryByRubrique($rubrique, $invisible);
            $params['pagination'] = $this->get('knp_paginator')->paginate(
                $query,
                $request->query->get('p', 1),
                self::ARTICLES_PER_PAGE
            );
        }
        elseif ($count == 2) {
            $type = $em->getRepository('VidalDrugBundle:ArtType')->rubriqueUrl($rubrique, $parts[1]);
            if (!$type || !$type->getEnabled()) {
                throw $this->createNotFoundException();
            }
            $params['type'] = $type;
            $params['categories'] = $em->getRepository('VidalDrugBundle:ArtCategory')->findByType($type);
            $query = $em->getRepository('VidalDrugBundle:Art')->getQueryByType($type, $invisible);
            $params['pagination'] = $this->get('knp_paginator')->paginate(
                $query,
                $request->query->get('p', 1),
                self::ARTICLES_PER_PAGE
            );
        }
        elseif ($count == 3) {
            $type = $em->getRepository('VidalDrugBundle:ArtType')->rubriqueUrl($rubrique, $parts[1]);
            $params['type'] = $type;
            $category = $em->getRepository('VidalDrugBundle:ArtCategory')->typeUrl($type, $parts[2]);
            $params['category'] = $category;

            if (!$type || !$type->getEnabled() || !$category || !$category->getEnabled()) {
                # Редиректы разделов специалистам в энциклопедию
                if ($parts[0] == 'endocrinologiya' && in_array($parts[1], array(
                        'hemoglobin-testing-system',
                        'insulinoviye-pompi',
                        'glucometri'
                    ))
                ) {
                    return $this->redirect($this->generateUrl('article', array(
                        'rubrique' => 'medicinskie-izdeliya',
                        'link' => $parts[2],
                    )), 301);
                }

                throw $this->createNotFoundException();
            }

            $query = $em->getRepository('VidalDrugBundle:Art')->getQueryByCategory($params['category'], $invisible);
            $params['pagination'] = $this->get('knp_paginator')->paginate(
                $query,
                $request->query->get('p', 1),
                self::ARTICLES_PER_PAGE
            );
        }

        # формируем заголовок страницы
        $titles = array();
        if (isset($params['article'])) {
            $title = $this->strip($params['article']->getTitle());
            $titles[] = $title;
            $params['ogTitle'] = $title;
            $params['description'] = $this->strip($params['article']->getAnnounce());
        }
        if (isset($params['category'])) {
            $titles[] = $params['category'];
        }
        if (isset($params['type'])) {
            $titles[] = $params['type'];
        }
        $titles[] = $params['rubrique'];
        $params['title'] = implode(' | ', $titles);

        # og-мета для разделов
        if (!isset($params['article'])) {
            $titles = array_reverse($titles);
            $params['ogTitle'] = implode('. ', $titles);
            $descriptions = array();
            if (isset($query)) {
                $arts = $query->getResult();
                foreach ($arts as $art) {
                    $descriptions[] = $this->strip($art->getTitle());
                }
                $params['description'] = implode('. ', $descriptions);
            }
        }

        # отображение отдельной статьи своим шаблоном
        if (isset($params['article'])) {
            if (!$params['article']->getEnabled() || !$params['article']->getRubrique()->getEnabled()) {
                throw $this->createNotFoundException();
            }
            if ($params['article']->getType() && !$params['article']->getType()->getEnabled()) {
                throw $this->createNotFoundException();
            }
            if ($params['article']->getCategory() && !$params['article']->getCategory()->getEnabled()) {
                throw $this->createNotFoundException();
            }
            if ($invisible == false && $params['article']->getInvisible()) {
                throw $this->createNotFoundException();
            }

            if (!$this->getUser() && $params['article']->getNoa()) {
                return $this->redirect($this->generateUrl('login'));
            }

            $descr = strip_tags($params['article']->getBody());
            $descr = str_replace(array('&laquo;', '&ndash;', '&nbsp;', '&raquo', '&laquo', '&copy', '&trade', '&reg', '&amp'), '', $descr);
            $descr = $this->truncateHtml($descr, 376);

            $title = trim($params['article']->getTitle())
                . ' . ' . trim(str_replace('Акушерство/Гинекология', 'Акушерство и Гинекология', $params['rubrique']->getTitle()))
                . '. Видаль справочник лекарственных препаратов';

            $params['seotitle'] = $title;
            $params['description'] = $descr;

            return $this->render('VidalMainBundle:Article:art_item.html.twig', $params);
        }

        $params['keywords'] = "";

        #seo
        if (isset($params['rubrique'])) {
            $seo = $params['rubrique']->getSeoTitle();
            if (!empty($seo)) {
                $params['seotitle'] = $params['rubrique']->getSeoTitle() . ' – справочник Видаль';
                $params['description'] = $params['rubrique']->getSeoDescription();
            }
        }

        if (isset($params['type'])) {
            $seo = $params['type']->getSeoTitle();
            if (!empty($seo)) {
                $params['seotitle'] = $params['type']->getSeoTitle() . ' – справочник Видаль';
                $params['description'] = $params['type']->getSeoDescription();
            }
        }

        if (isset($params['category'])) {
            $seo = $params['category']->getSeoTitle();
            if (!empty($seo)) {
                $params['seotitle'] = $params['category']->getSeoTitle() . ' – справочник Видаль';
                $params['description'] = $params['category']->getSeoDescription();
            }
        }

        return $params;
    }

    function truncateHtml($text, $length = 200, $ending = '', $exact = false, $considerHtml = true)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = mb_strlen($ending);
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    }
                    else {
                        if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                            // delete tag from $open_tags list
                            $pos = array_search($tag_matchings[1], $open_tags);
                            if ($pos !== false) {
                                unset($open_tags[$pos]);
                            }
                            // if tag is an opening tag
                        }
                        else {
                            if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                                // add tag to the beginning of $open_tags list
                                array_unshift($open_tags, strtolower($tag_matchings[1]));
                            }
                        }
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += mb_strlen($entity[0]);
                            }
                            else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                }
                else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        }
        else {
            if (mb_strlen($text) <= $length) {
                return $text;
            }
            else {
                $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= ' ' . $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return trim($truncate);
    }

    public function checkRole()
    {
        $security = $this->get('security.context');

        if (!$security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('login'), 307);
        }
        elseif (!$security->isGranted('ROLE_DOCTOR')) {
            return $this->redirect($this->generateUrl('confirm'), 307);
        }

        return null;
    }

    private function strip($string)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));
        $string = preg_replace('/&nbsp;|®|™/', '', $string);

        return $string;
    }

    private function getProductIds($products)
    {
        $productIds = array();

        foreach ($products as $product) {
            $productIds[] = $product['ProductID'];
        }

        return $productIds;
    }
}
