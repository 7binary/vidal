<?php

namespace Vidal\DrugBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Vidal\DrugBundle\Command\GeneratorProductLettersCommand;
use Vidal\DrugBundle\Entity\ATC;
use Vidal\DrugBundle\Entity\Nozology;

class DrugsController extends Controller
{
    const PHARM_PER_PAGE = 500;
    const KFG_PER_PAGE = 150;
    const PRODUCTS_PER_PAGE = 500;

    private $nozologies;

    /**
     * @Route("/drugs/atc-tree", name="atc_tree")
     * @Template("VidalDrugBundle:Drugs:atc_tree.html.twig")
     */
    public function atcTreeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $choices = $em->getRepository('VidalDrugBundle:ATC')->getChoices();
        $atcCode = $request->query->get('c', null);

        $params = array(
            'menu_drugs' => 'atc',
            'title' => 'АТХ',
            'ATCCode' => $atcCode,
            'choices' => $choices,
        );

        return $params;
    }

    /** @Route("/poisk_preparatov/lat_{url}", requirements={"url"=".+"}) */
    public function redirectLat($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        return $this->redirect($this->generateUrl('atc_item', array('ATCCode' => $url)), 301);
    }

    /**
     * Препараты по коду АТХ
     *
     * @Route("/drugs/atc/{ATCCode}/{search}", name="atc_item", options={"expose":true})
     * @Template("VidalDrugBundle:Drugs:atc_item.html.twig")
     */
    public function atcItemAction($ATCCode, $search = 0)
    {
        $em = $this->getDoctrine()->getManager('drug');
        /** @var ATC $atc */
        $atc = $em->getRepository('VidalDrugBundle:ATC')->findOneByATCCode($ATCCode);

        if (!$atc) {
            throw $this->createNotFoundException();
        }

        if (preg_match('#[A-Z]#', $ATCCode)) {
            return $this->redirect($this->generateUrl('atc_item', array(
                'ATCCode' => strtolower($ATCCode)
            )), 301);
        }

        # все продукты по ATC-коду и отсеиваем дубли
        $products = $em->getRepository('VidalDrugBundle:Product')->findByATCCode($ATCCode);

        if (empty($products)) {
            throw $this->createNotFoundException();
        }

        $params = array(
            'atc' => $atc,
            'products' => $products,
            'title' => $this->strip($atc->getRusName()) . ' - ' . $atc->getATCCode() . ' | АТХ',
        );

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
        }

        return $search ? $this->render('VidalDrugBundle:Drugs:search_atc_item.html.twig', $params) : $params;
    }

    /**
     * @Route("/drugs/atc", name="atc")
     * @Route("/drugs/atcl/{l}", name="atcl")
     * @Template("VidalDrugBundle:Drugs:atc.html.twig")
     */
    public function atcAction(Request $request, $l = null)
    {
        if ($lGet = $request->query->get('l', null)) {
            if ($l == null) {
                return $this->redirect($this->generateUrl('atcl', array('l' => strtolower($lGet))), 301);
            }
        }

        $em = $this->getDoctrine()->getManager('drug');
        $q = $request->query->get('q', null);
        $atcCode = $request->query->get('ATCCode', '');

        $params = array(
            'menu_drugs' => 'atc',
            'title' => 'АТХ',
            'keywords' => '',
            'l' => $l,
            'q' => $q,
            'atcCode' => $atcCode,
        );

        # если указан АТС-код, то показываем страницу по букве
        if (!empty($atcCode)) {
            $l = $params['l'] = substr($atcCode, 0, 1);
            $params['urlCanonical'] = 'https://www.vidal.ru/drugs/atc';
        }

        if ($l) {
            /** @var ATC[] $codesByLetter */
            $codesByLetter = $em->getRepository('VidalDrugBundle:ATC')->findByLetter($l);
            $codeByLetter = array_shift($codesByLetter);
            $params['codeByLetter'] = $codeByLetter;
            $params['codesByLetter'] = $codesByLetter;
            $params['seotitle'] = $this->strip($codeByLetter->getRusName())
                . " - Анатомо-Терапевтически-Химическая система классификации (АТХ)";
        }
        elseif ($q) {
            $params['atcCodes'] = mb_strlen($q, 'utf-8') < 2
                ? null
                : $em->getRepository('VidalDrugBundle:ATC')->findByQuery($q);
        }
        else {
            $params['showTree'] = true;
        }

        $params['codesMenu'] = explode(' ', 'a b c d g h j m n p r s v');

        return $params;
    }

    /**
     * [AJAX] Подгрузка дерева ATC
     *
     * @Route("/drugs/atc-ajax", name="atc_ajax", options={"expose":true})
     */
    public function atcAjaxAction(Request $request)
    {
        if ($request->request->has('root')) {
            $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'atc.json';
            $json = json_decode(file_get_contents($file), true);
            $root = $request->request->get('root');
            $data = $json[$root]['children'];

            return new JsonResponse($data);
        }

        return new JsonResponse();
    }

    /**
     * Функция генерации дерева с кодами ATC
     *
     * @Route("/drugs/atc-generator", name="atc_generator")
     * @Template("VidalDrugBundle:Drugs:atc_generator.html.twig")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function atcGeneratorAction()
    {
        $em = $this->getDoctrine()->getManager('drug');
        $repo = $em->getRepository('VidalDrugBundle:ATC');
        $codes = $repo->findForTree();

        return array('codes' => $codes);
    }

    /**
     * @Route("/poisk_preparatov/lkf_{code}.htm", requirements={"code"=".+"})
     * @Route("/poisk_preparatov/kf_{code}.htm", requirements={"code"=".+"})
     */
    public function kfuRedirect($code)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $kfu = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findOneByCode($code);

        if (!$kfu) {
            return $this->redirect($this->generateUrl('index'), 301);
        }

        return $this->redirect($this->generateUrl('kfu_item', array(
            'code' => $code,
        )), 301);
    }

    /**
     * Препараты по КФУ
     *
     * @Route("/drugs/clinic-pointer/{code}", name="kfu_item", options={"expose":true})
     * @Template("VidalDrugBundle:Drugs:kfu_item.html.twig")
     */
    public function kfuItemAction($code)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $repo = $em->getRepository('VidalDrugBundle:ClinicoPhPointers');
        $kfu = $repo->findOneByCode($code);

        if (!$kfu) {
            throw $this->createNotFoundException();
        }

        $ClPhPointerID = $kfu->getClPhPointerID();
        $moleculeIdsUsed = array();

        $params = array(
            'menu_drugs' => 'kfu',
            'kfu' => $kfu,
            'title' => $this->strip($kfu->getName()) . ' - ' . $kfu->getCode() . ' | Клинико-фармакологические указатели',
        );

        $products = $em->getRepository('VidalDrugBundle:Product')->findByKfu($ClPhPointerID);

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['products'] = $products;
            $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);

            ####################################################################################################
            # группируем препараты по активному веществу
            $groups = array();
            $unusedProducts = array();
            $moleculeRepo = $em->getRepository('VidalDrugBundle:Molecule');
            $molecules = $moleculeRepo->findByProductIds($productIds);

            foreach ($products as $product) {
                $moleculeIds = $moleculeRepo->idsByProduct($product['ProductID']);

                if (empty($moleculeIds) || count($moleculeIds) > 3 || in_array(1144, $moleculeIds) || in_array(2203, $moleculeIds)) {
                    $unusedProducts[] = $product;
                    continue;
                }

                $group = implode('-', $moleculeIds);

                if (isset($groups[$group])) {
                    $groups[$group]['products'][] = $product;
                }
                else {
                    $groups[$group]['products'] = array($product);
                    $groups[$group]['moleculeIds'] = $moleculeIds;
                }
            }

            $params['molecules'] = $molecules;
            $params['groups'] = $groups;
            $params['unusedProducts'] = $unusedProducts;

            foreach ($molecules as $molecule) {
                $moleculeIdsUsed[] = $molecule['MoleculeID'];
            }
        }

        $params['moleculesRest'] = $em->getRepository('VidalDrugBundle:Molecule')->findByKfu($ClPhPointerID, $moleculeIdsUsed);

        return $params;
    }

    /**
     * @Route("/drugs/clinic-pointers/{l}", name="kfu")
     * @Template("VidalDrugBundle:Drugs:kfu.html.twig")
     */
    public function kfuAction(Request $request, $l = null)
    {
        if ($lGet = $request->query->get('l', null)) {
            if ($l == null) {
                return $this->redirect($this->generateUrl('kfu', array('l' => strtolower($lGet))), 301);
            }
        }

        $em = $this->getDoctrine()->getManager('drug');
        $q = $request->query->get('q', null);

        $params = array(
            'menu_drugs' => 'kfu',
            'title' => 'Клинико-фармакологические указатели',
            'l' => $l,
            'q' => $q,
        );

        if ($l) {
            $codesByLetter = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findByLetter($l);
            $params['codeByLetter'] = array_shift($codesByLetter);
            $params['codesByLetter'] = $codesByLetter;
        }
        elseif ($q) {
            $params['codes'] = mb_strlen($q, 'utf-8') < 2
                ? null
                : $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findByQuery($q);
        }
        else {
            $params['showTree'] = true;
        }

        if ($request->query->has('show')) {
            $em = $this->getDoctrine()->getManager('drug');
            $show = $request->query->get('show', null);
            $showKfu = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findOneById($show);
            if ($showKfu) {
                $showBaseKfu = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findBase($showKfu);
                if ($showBaseKfu) {
                    $params['showKfu'] = $showKfu;
                    $params['showBaseKfu'] = $showBaseKfu;
                }
            }
        }

        $params['codesMenu'] = explode(' ', '01 02 03 04 05 06 07 08 09 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34');

        return $params;
    }

    /**
     * [AJAX] Подгрузка дерева КФУ
     *
     * @Route("/drugs/kfu-ajax", name="kfu_ajax", options={"expose":true})
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
     * Функция генерации дерева с кодами КФУ
     *
     * @Route("/drugs/kfu-generator", name="kfu_generator")
     * @Template("VidalDrugBundle:Drugs:kfu_generator.html.twig")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function kfuGeneratorAction()
    {
        $em = $this->getDoctrine()->getManager('drug');
        $codes = $em->getRepository('VidalDrugBundle:ClinicoPhPointers')->findForTree();

        return array('codes' => $codes);
    }

    /**
     * Список компаний
     *
     * @Route("/drugs/pharm-groups/{l}", name="pharm")
     * @Template("VidalDrugBundle:Drugs:pharm.html.twig")
     */
    public function pharmAction(Request $request, $l = null)
    {

        $em = $this->getDoctrine()->getManager('drug');
        $q = $request->query->get('q', null);
        $p = $request->query->get('p', 1);

        $codesMenu = array('А' => 'rus-a', 'Б' => 'rus-b', 'В' => 'rus-v', 'Г' => 'rus-g', 'Д' => 'rug-d',
            'Ж' => 'rus-zh', 'З' => 'rus-z',
            'И' => 'rus-i', 'К' => 'rus-k', 'Л' => 'rus-l', 'М' => 'rus-m', 'Н' => 'rus-n', 'О' => 'rus-o', 'П' => 'rus-p',
            'Р' => 'rus-r', 'С' => 'rus-s', 'Т' => 'rus-t', 'У' => 'rus-u', 'Ф' => 'rus-f',
            'Х' => 'rus-h', 'Ц' => 'rus-c', 'Э' => 'rus-e', 'Я' => 'rus-ya', 'H' => 'h');

        $codesMenuR = array_flip($codesMenu);

        if ($lGet = $request->query->get('l', null)) {
            if ($l == null) {
                $l = empty($codesMenu[$lGet]) ? null : $codesMenu[$lGet];
                if ($l) {
                    return $this->redirect($this->generateUrl('pharm', array('l' => $l)), 301);
                }
            }
        }

        $rusLetter = empty($codesMenuR[$l]) ? null : $codesMenuR[$l];

        if ($l) {
            $query = $em->getRepository('VidalDrugBundle:PhThGroups')->getQueryByLetter($rusLetter);
        }
        elseif ($q) {
            $query = $em->getRepository('VidalDrugBundle:PhThGroups')->findByQueryString($q);
        }
        else {
            $query = $em->getRepository('VidalDrugBundle:PhThGroups')->getQuery();
        }

        $params = array(
            'menu_drugs' => 'pharm',
            'title' => 'Фармако-терапевтические группы',
            'q' => $q,
            'l' => $l,
            'rusLetter' => $rusLetter,
            'pagination' => $this->get('knp_paginator')->paginate($query, $p, self::PHARM_PER_PAGE),
            'codesMenu' => $codesMenu,
            'codesMenuR' => $codesMenuR,
        );

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        return $params;
    }

    /**
     * Список препаратов по фармако-терапевтической группе
     *
     * @Route("/drugs/pharm-group/{id}/{search}", name="pharm_item", defaults={"id":"\d+"})
     * @Template("VidalDrugBundle:Drugs:pharm_item.html.twig")
     */
    public function pharmItemAction($id, $search = 0)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $phthgroup = $em->getRepository('VidalDrugBundle:PhThGroups')->findById($id);

        if ($phthgroup === null) {
            throw $this->createNotFoundException();
        }

        $params = array(
            'phthgroup' => $phthgroup,
            'title' => $phthgroup['Name'] . ' | Фармако-терапевтические группы',
        );

        $products = $em->getRepository('VidalDrugBundle:Product')->findByPhThGroup($id);

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['products'] = $products;
            $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
        }

        return $search ? $this->render('VidalDrugBundle:Drugs:search_pharm_item.html.twig', $params) : $params;
    }

    /** @Route("/poisk_preparatov/lno_{url}", requirements={"url"=".+"}) */
    public function redirectNosology($url)
    {
        if ($pos = strrpos($url, '.')) {
            $url = substr($url, 0, $pos);
        }

        return $this->redirect($this->generateUrl('nosology_item', array('Code' => $url)), 301);
    }

    /**
     * Список препаратов и активных веществ по показанию (Nozology)
     *
     * @Route("/drugs/nosology/{Code}/{search}", name="nosology_item", options={"expose":true})
     * @Template("VidalDrugBundle:Drugs:nosology_item.html.twig")
     */
    public function nosologyItemAction(Request $request, $Code, $search = 0)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $nozology = $em->getRepository('VidalDrugBundle:Nozology')->findOneByCode($Code);

        if ($nozology === null) {
            throw $this->createNotFoundException();
        }

        if (preg_match('#[A-Z]#', $Code)) {
            return $this->redirect($this->generateUrl('nosology_item', array(
                'Code' => strtolower($Code)
            )), 301);
        }

        # надо найти нозологический код у этой назологии и родительской
        $nozologyCodes = array($nozology->getNozologyCode());
        if ($parent = $nozology->getParent()) {
            /** @var Nozology $parent */
            if ($parent->getLevel()) {
                $nozologyCodes[] = $parent->getNozologyCode();
                if ($grandparent = $parent->getParent()) {
                    if ($grandparent->getLevel()) {
                        $nozologyCodes[] = $grandparent->getNozologyCode();
                    }
                }
            }
        }

        $params = array(
            'nozology' => $nozology,
            'title' => $nozology->getName() . ' | ' . 'Нозологический указатель',
            'articles' => $em->getRepository('VidalDrugBundle:Article')->findByNozology($nozologyCodes),
            'arts' => $em->getRepository('VidalDrugBundle:Art')->findByNozology($nozologyCodes),
            'publications' => $em->getRepository('VidalDrugBundle:Publication')->findByNozology($nozologyCodes),
        );

        $params['molecules'] = $em->getRepository('VidalDrugBundle:Molecule')->findByNozologyCode($Code);
        $documents = $em->getRepository('VidalDrugBundle:Document')->findByNozologyCode($Code);

        if (empty($documents)) {
            throw $this->createNotFoundException();
        }

        $products1 = $em->getRepository('VidalDrugBundle:Product')->findByDocuments25($documents);
        $products2 = $em->getRepository('VidalDrugBundle:Product')->findByDocuments4($documents);
        $products = array();

        # надо слить продукты, исключая повторения и отсортировать по названию
        foreach ($products1 as $id => $product) {
            $products[] = $product;
        }
        foreach ($products2 as $id => $product) {
            if (!isset($products1[$id])) {
                $products[] = $product;
            }
        }

        if (empty($products)) {
            throw $this->createNotFoundException();
        }

        usort($products, function ($a, $b) {
            return strcmp($a['RusName'], $b['RusName']);
        });

        $productIds = $this->getProductIds($products);
        $params['products'] = $products;
        $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
        $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);

        return $search ? $this->render('VidalDrugBundle:Drugs:search_nosology_item.html.twig', $params) : $params;
    }

    /**
     * @Route("/drugs/nosology", name="nosology")
     * @Route("/drugs/nosologyl/{l}", name="nosologyl")
     * @Template("VidalDrugBundle:Drugs:nosology.html.twig")
     */
    public function nosologyAction(Request $request, $l = null)
    {
        if ($lGet = $request->query->get('l', null)) {
            if ($l == null) {
                return $this->redirect($this->generateUrl('nosologyl', array('l' => strtolower($lGet))), 301);
            }
        }

        $em = $this->getDoctrine()->getManager('drug');
        $q = $request->query->get('q', null);

        $params = array(
            'menu_drugs' => 'nosology',
            'title' => 'Нозологический указатель',
            'l' => $l,
            'q' => $q,
        );

        if ($l) {
            $upperL = strtoupper($l);
            $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'nosology.json';
            $json = json_decode(file_get_contents($file), true);
            $params['codeByLetter'] = $json[$upperL];
            $this->orderNozologyCodes($json[$upperL]['children']);
            $params['codesByLetter'] = $this->nozologies;
        }
        elseif ($q) {
            $params['codes'] = mb_strlen($q, 'utf-8') < 2
                ? null
                : $em->getRepository('VidalDrugBundle:Nozology')->findByQuery($q);
        }
        else {
            $params['showTree'] = true;
        }

        $params['codesMenu'] = explode(' ', 'a00-b99 c00-d48 d50-d89 e00-e90 f00-f99 g00-g99 h00-h59 h60-h95 i00-i99 j00-j99 k00-k93 l00-l99 m00-m99 n00-n99 o00-o99 p00-p96 q00-q99 r00-r99 s00-t98 z00-z99');

        return $params;
    }

    private function orderNozologyCodes($codes)
    {
        foreach ($codes as $code) {
            $this->nozologies[] = array(
                'code' => $code['code'],
                'Level' => $code['Level'],
                'text' => $code['text'],
                'countProducts' => $code['countProducts'],
            );

            if (isset($code['children'])) {
                $this->orderNozologyCodes($code['children']);
            }
        }
    }

    /**
     * @Route("/drugs/nosology-tree", name="nosology_tree")
     * @Template("VidalDrugBundle:Drugs:nosology_tree.html.twig")
     */
    public function nosologyTreeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $choices = $em->getRepository('VidalDrugBundle:Nozology')->getChoices();
        $nosologyCode = $request->query->get('c', null);

        $params = array(
            'menu_drugs' => 'nosology',
            'title' => 'Нозологический указатель',
            'nosologyCode' => $nosologyCode,
            'choices' => $choices,
        );

        return $params;
    }

    /**
     * [AJAX] Подгрузка дерева Нозологических указателей
     * @Route("/drugs/nosology-ajax", name="nosology_ajax", options={"expose":true})
     */
    public function nosologyAjaxAction(Request $request)
    {
        if ($request->request->has('root')) {
            $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'nosology.json';
            $json = json_decode(file_get_contents($file), true);
            $root = $request->request->get('root');
            $data = $json[$root]['children'];

            return new JsonResponse($data);
        }

        return new JsonResponse();
    }

    /**
     * Функция генерации дерева нозологических указателей
     *
     * @Route("/drugs/nosology-generator", name="nosology_generator")
     * @Template("VidalDrugBundle:Drugs:nosology_generator.html.twig")
     * @Secure(roles="ROLE_ADMIN")
     */
    public function nosologyGeneratorAction()
    {
        $em = $this->getDoctrine()->getManager('drug');
        $nozologies = $em->getRepository('VidalDrugBundle:Nozology')->findForTree();

        return array('codes' => $nozologies);
    }

    /**
     * @Route("/drugs/clinic-groups", name="clinic_groups")
     * @Template("VidalDrugBundle:Drugs:clinic_groups.html.twig")
     */
    public function clinicGroupsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $q = $request->query->get('q', null);
        $l = $request->query->get('l', null);
        $p = $request->query->get('p', 1);

        if ($l) {
            $query = $em->getRepository('VidalDrugBundle:ClPhGroups')->findByLetter($l);
        }
        elseif ($q) {
            $query = $em->getRepository('VidalDrugBundle:ClPhGroups')->findByQuery($q);
        }
        else {
            $query = $em->getRepository('VidalDrugBundle:ClPhGroups')->getQuery();
        }

        $params = array(
            'menu_drugs' => 'clinic_groups',
            'title' => 'Клинико-фармакологические группы',
            'q' => $q,
            'l' => $l,
            'pagination' => $this->get('knp_paginator')->paginate($query, $p, self::KFG_PER_PAGE),
            'codesMenu' => explode(' ', 'А Б В Г Д Ж З И К М Н О П Р С Т Ф Ц Ч Э'),
        );

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        return $params;
    }

    /**
     * @Route("/drugs/clinic-group/{id}/{search}", name="clinic_group")
     * @Template("VidalDrugBundle:Drugs:clinic_group.html.twig")
     */
    public function clinicGroupAction($id, $search = 0)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $clphGroup = $em->getRepository('VidalDrugBundle:ClPhGroups')->findOneById($id);

        if (!$clphGroup) {
            throw $this->createNotFoundException();
        }

        $params = array(
            'title' => $this->strip($clphGroup->getName()) . ' | Клинико-фармакологические группы',
            'clphGroup' => $clphGroup,
        );

        $products = $em->getRepository('VidalDrugBundle:Product')->findByClPhGroupID($id);

        if (!empty($products)) {
            $productIds = $this->getProductIds($products);
            $params['products'] = $products;
            $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            $params['infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByProducts($products);
        }

        return $search ? $this->render('VidalDrugBundle:Drugs:search_clinic_group.html.twig', $params) : $params;
    }

    /**
     * @Route("/drugs/companies/{l}", name="companies")
     * @Template("VidalDrugBundle:Drugs:companies.html.twig")
     */
    public function companiesAction(Request $request, $l = null)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $q = $request->query->get('q', null);
        $p = $request->query->get('p', 1);
        $type = $request->query->get('type', null);

        $codesMenuEn = explode(' ', 'a b c d e f g h i j k l m n o p q r s t u v w x y z 4');
        $codesMenu = array('А' => 'rus-a', 'Б' => 'rus-b', 'В' => 'rus-v', 'Г' => 'rus-g', 'Д' => 'rus-d', 'Е' => 'rus-e',
            'З' => 'rus-z', 'И' => 'rus-i', 'Й' => 'rus-j', 'К' => 'rus-k', 'Л' => 'rus-l', 'М' => 'rus-m',
            'Н' => 'rus-n', 'О' => 'rus-o', 'П' => 'rus-p',
            'Р' => 'rus-r', 'С' => 'rus-s', 'Т' => 'rus-t', 'У' => 'rus-u', 'Ф' => 'rus-f',
            'Х' => 'rus-h', 'Ц' => 'rus-c', 'Ч' => 'rus-ch', 'Ш' => 'rus-sh', 'Щ' => 'rus-shch',
            'Э' => 'rus-eh', 'Ю' => 'rus-yu', 'Я' => 'rus-ya');
        $codesMenuR = array_flip($codesMenu);
        $rusLetter = empty($codesMenuR[$l]) ? null : $codesMenuR[$l];

        if ($lGet = $request->query->get('l', null)) {
            if ($l == null) {
                $l = empty($codesMenu[$lGet]) ? null : $codesMenu[$lGet];
                if ($l) {
                    return $this->redirect($this->generateUrl('companies', array('l' => $l)), 301);
                }
                $lGet = strtolower($lGet);
                return $this->redirect($this->generateUrl('companies', array('l' => $lGet)), 301);
            }
        }

        $title = 'Фармацевтические компании и представительства';
        if ($l) {
            $title .= ' на букву ' . ($rusLetter ? mb_strtoupper($rusLetter, 'utf-8') : strtoupper($l));
        }

        $params = array(
            'menu_drugs' => 'companies',
            'q' => $q,
            'l' => $l,
            'rusLetter' => $rusLetter,
            'codesMenu' => $codesMenu,
            'codesMenuEn' => $codesMenuEn,
            'title' => $title,
        );

        if ($l) {
            $params['search_companies'] = $em->getRepository('VidalDrugBundle:Company')->findByLetter($rusLetter);
            $params['search_infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByLetter($rusLetter);
        }
        elseif ($q) {
            $params['search_companies'] = $em->getRepository('VidalDrugBundle:Company')->findByQuery($q);
            $params['search_infoPages'] = $em->getRepository('VidalDrugBundle:InfoPage')->findByQuery($q);
        }
        else {
            if (!$type || $type == 'c') {
                $query = $em->getRepository('VidalDrugBundle:Company')->getQuery($q);
                $params['pagination_companies'] = $this->get('knp_paginator')->paginate($query, $p, 40, array('type' => 'c'));
            }

            if (!$type || $type == 'i') {
                $query = $em->getRepository('VidalDrugBundle:InfoPage')->getQuery($q);
                $params['pagination_infoPages'] = $this->get('knp_paginator')->paginate($query, $p, 40, array('type' => 'i'));
            }
        }

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        return $params;
    }

    public function syllablesAction($t, $l, $n)
    {
        if ($t == 'm') {
            return new Response();
        }

        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR;
        $file = "{$path}syllables_$t.json";
        $syllables = json_decode(file_get_contents($file), true);
        $letters = array_keys($syllables);

        return $this->render('VidalDrugBundle:Drugs:products_syllables.html.twig', array(
            'syllables' => $syllables,
            'letters' => $letters,
            'l' => $l,
            't' => $t,
            'n' => $n,
        ));
    }

    /** @Route("/ajax-syllables", name="ajax_syllables", options={"expose":true}) */
    public function ajaxSyllablesAction(Request $request)
    {
        if ($request->isXmlHttpRequest() == false) {
            throw $this->createNotFoundException();
        }

        $t = $request->query->get('t', 'p');
        $l = $request->query->get('l', null);

        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR;
        $file = "{$path}syllables_$t.json";
        $syllables = json_decode(file_get_contents($file), true);
        $letters = array_keys($syllables);

        $view = $this->renderView('VidalDrugBundle:Drugs:products_syllables.html.twig', array(
            'syllables' => $syllables,
            'letters' => $letters,
            'l' => $l,
            't' => $t,
        ));

        return new JsonResponse($view);
    }

    public function tableAction($t, $n)
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR;
        $file = "{$path}table_$t.json";
        $table = json_decode(file_get_contents($file), true);
        $file = "{$path}syllables_$t.json";
        $syllables = json_decode(file_get_contents($file), true);
        $letters = array_keys($syllables);

        return $this->render('VidalDrugBundle:Drugs:products_table.html.twig', array(
            'table' => $table,
            'letters' => $letters,
            't' => $t,
            'n' => $n,
        ));
    }

    /**
     * @Route("/drugs/products/{t}/{l}", name="products", options={"expose":true})
     * @Template("VidalDrugBundle:Drugs:products.html.twig")
     */
    public function productsAction(Request $request, $t = 'p', $l = null)
    {
        $get_t = $request->query->get('t', null);
        $get_l = $request->query->get('l', null);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $p = $request->query->get('p', 1); // номер страницы
        $n = $request->query->has('n') && $request->query->get('n') != 'false'; // только безрецептурные препараты

        if ($get_t || $get_l) {
            return $this->redirect($this->generateUrl('products', array(
                't' => $get_t,
                'l' => $get_l,
            )), 301);
        }

        $letters = $this->get('product.service')->getLetters($t, $n);

        # проверка прежнего URL с русской буквой
        if (!empty($letters)) {
            foreach ($letters as $ruLetter => $info) {
                if ($ruLetter == $l && $l!=$info['trans']) {
                    return $this->redirect($this->generateUrl('products', array(
                        't' => $t,
                        'l' => $info['trans'],
                    )), 301);
                }
                if (!empty($info['subs'])) {
                    foreach ($info['subs'] as $subLetter => $subTrans) {
                        if ($subLetter == $l) {
                            return $this->redirect($this->generateUrl('products', array(
                                't' => $t,
                                'l' => $subTrans,
                            )), 301);
                        }
                    }
                }
            }
        }

        # проверка наличия русских букв оставшихся
        if ($l && preg_match('#[а-я]#ui', $l)) {
            return $this->redirect($this->generateUrl('products', array(
                't' => $t,
                'l' => GeneratorProductLettersCommand::transRus($l)
            )), 301);
        }

        # поиск русской буквы текущей и всех под-букв для таблицы
        $currentRusLetter = null;
        $allSubLetters = array();

        if (!empty($letters)) {
            foreach ($letters as $ruLetter => $info) {
                if ($info['trans'] == $l) {
                    $currentRusLetter = $ruLetter;
                    break;
                }

                if (!empty($info['subs'])) {
                    foreach ($info['subs'] as $subLetter => $subTrans) {
                        $l1 = mb_substr($subLetter, 0, 1);
                        $l2 = mb_substr($subLetter, 1, 1);

                        if (!isset($allSubLetters[$l2])) {
                            $allSubLetters[$l2] = array();
                            foreach ($letters as $_ruLetter => $_info) {
                                $allSubLetters[$l2][$_ruLetter] = null;
                            }
                        }
                        $allSubLetters[$l2][$l1] = array('subLetter' => $subLetter, 'subTrans' => $subTrans);

                        if ($subTrans == $l) {
                            $currentRusLetter = $subLetter;
                            break;
                        }
                    }
                }
            }
        }

        if ($request->query->get('n') === 'false') {
            return $p == 1
                ? $this->redirect($this->generateUrl('products', array('t' => $t, 'l' => $l)))
                : $this->redirect($this->generateUrl('products', array('t' => $t, 'l' => $l, 'p' => $p)));
        }

        $params = array(
            't' => $t,
            'p' => $p,
            'l' => $l,
            'n' => $n,
            'letters' => $letters,
            'allSubLetters' => $allSubLetters,
            'currentRusLetter' => $currentRusLetter,
            'menu_drugs' => 'products',
            'title' => 'Поиск препаратов по алфавиту',
            'seotitle' => 'Поиск препаратов, биологически активных добавок (БАД) и медицинских изделий по алфавиту',
        );

        if ($l) {
            $multi = mb_strlen($l, 'utf-8') > 1;
            if ($t == 'p') {
                $params['description'] = $p == 1
                    ? 'Поиск лекарственных препаратов по алфавиту. Буква ' . $currentRusLetter
                    . '. Справочник лекарственных препаратов, содержащий подробные описания и инструкции по применению лекарственных препаратов.'
                    : 'Поиск лекарственных препаратов по алфавиту. Буква ' . $currentRusLetter . '. Страница ' . $p
                    . '. Справочник лекарственных препаратов, содержащий подробные описания и инструкции по применению лекарственных препаратов.';
            }
            if ($t == 'b') {
                $params['description'] = $p == 1
                    ? 'Поиск биологически активных добавок (БАД) и медицинских изделий по алфавиту. Буква ' . $currentRusLetter . '.'
                    : 'Поиск биологически активных добавок (БАД) и медицинских изделий по алфавиту. Буква ' . $currentRusLetter . '. Страница ' . $p . '.';
            }
            if ($t == 'o') {
                $params['description'] = $p == 1
                    ? 'Поиск лекарственных препаратов, биологически активных добавок (БАД) и медицинских изделий по алфавиту. Буква ' . $currentRusLetter . '.'
                    : 'Поиск лекарственных препаратов и биологически активных добавок (БАД) и медицинских изделий по алфавиту. Буква ' . $currentRusLetter . '. Страница ' . $p . '.';
            }
            if ($t == 'n') {
                $params['description'] = $p == 1
                    ? 'Поиск лечебного питания по алфавиту. Буква ' . $currentRusLetter . '.'
                    : 'Поиск лечебного питания по алфавиту. Буква ' . $currentRusLetter . '. Страница ' . $p . '.';
            }

            # определяем SEO title
            if ($n) {
                if ($t == 'b') {
                    $params['seotitle'] = $multi
                        ? 'БАД и медицинские изделия на "' . $currentRusLetter . '..."'
                        : 'БАД и медицинские изделия на букву ' . $currentRusLetter;

                }
                elseif ($t == 'o') {
                    $params['seotitle'] = $multi
                        ? 'Безрецептурные препараты, БАД и медицинские изделия на "' . $currentRusLetter . '..."'
                        : 'Безрецептурные препараты, БАД и медицинские изделия на букву ' . $currentRusLetter;
                }
                elseif ($t == 'n') {
                    $params['seotitle'] = $multi
                        ? 'Лечебное питание на "' . $currentRusLetter . '..."'
                        : 'Лечебное питание на букву ' . $currentRusLetter;
                }
                else {
                    $params['seotitle'] = $multi
                        ? 'Безрецептурные препараты на "' . $currentRusLetter . '..."'
                        : 'Безрецептурные препараты на букву ' . $currentRusLetter;
                }
            }
            else {
                if ($t == 'b') {
                    $params['seotitle'] = $multi
                        ? 'БАД и медицинские изделия на "' . $currentRusLetter . '..."'
                        : 'БАД и медицинские изделия на букву ' . $currentRusLetter;
                }
                elseif ($t == 'o') {
                    $params['seotitle'] = $multi
                        ? 'Лекарственные препараты, БАД и медицинские изделия на "' . $currentRusLetter . '..."'
                        : 'Лекарственные препараты, БАД и медицинские изделия на букву ' . $currentRusLetter;
                }
                elseif ($t == 'n') {
                    $params['seotitle'] = $multi
                        ? 'Лечебное питание на "' . $currentRusLetter . '..."'
                        : 'Лечебное питание на букву ' . $currentRusLetter;
                }
                else {
                    $params['seotitle'] = $multi
                        ? 'Лекарственные препараты на "' . $currentRusLetter . '..."'
                        : 'Лекарственные препараты на букву ' . $currentRusLetter;
                }
            }
        }
        else {
            if ($t == 'p') {
                $params['description'] = 'Поиск лекарственных препаратов по алфавиту'
                    . '. Справочник лекарственных препаратов, содержащий подробные описания и инструкции по применению лекарственных препаратов.';
                $params['seotitle'] = 'Поиск лекарственных препаратов по алфавиту';
            }
            if ($t == 'b') {
                $params['description'] = 'Поиск биологически активных добавок (БАД) и медицинских изделий по алфавиту.';
                $params['seotitle'] = 'Поиск биологически активных добавок (БАД) и медицинских изделий по алфавиту';
            }
            if ($t == 'o') {
                $params['description'] = 'Поиск лекарственных препаратов, биологически активных добавок (БАД) и медицинских изделий по алфавиту.';
                $params['seotitle'] = 'Поиск лекарственных препаратов, биологически активных добавок (БАД) и медицинских изделий по алфавиту';
            }
            if ($t == 'n') {
                $params['description'] = 'Поиск лечебного питания по алфавиту.';
                $params['seotitle'] = 'Поиск лечебного питания по алфавиту';
            }
        }

        # БАДы и MI только безрецептурные
        if ($t == 'b') {
            $n = false;
            $params['menu_left'] = 'bads';
        }
        elseif ($t == 'n') {
            $params['menu_left'] = 'nutr';
        }

        if ($l != null || $t == 'm' || $t == 'n') {
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $em->getRepository('VidalDrugBundle:Product')->getQueryByLetter($currentRusLetter, $t, $n),
                $p,
                self::PRODUCTS_PER_PAGE
            );

            $products = $pagination->getItems();
            $params['pagination'] = $pagination;

            if (!empty($products)) {
                $productIds = array();

                foreach ($products as $product) {
                    $productIds[] = $product['ProductID'];
                }

                $params['products'] = $products;
                $params['indications'] = $em->getRepository('VidalDrugBundle:Document')->findIndicationsByProductIds($productIds);
                $params['companies'] = $em->getRepository('VidalDrugBundle:Company')->findByProducts($productIds);
            }
        }

        $l_first = null;
        if (!empty($l)) {
            $l_first = mb_substr($currentRusLetter, 0, 1, 'utf-8');
        }

        foreach ($letters as $firstLetter => &$paramsLetter) {
            if ($l_first == $firstLetter) {
                $paramsLetter['active'] = true;
            }
        }

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

        return $params;
    }

    /**
     * @Route("/drugs/disease/{l}", name="disease")
     * @Template("VidalDrugBundle:Drugs:disease.html.twig")
     */
    public function diseaseAction(Request $request, $l = null)
    {
        $q = $request->query->get('q', null);
        $em = $this->getDoctrine()->getManager('drug');

        $codesMenu = array('А' => 'rus-a', 'Б' => 'rus-b', 'В' => 'rus-v', 'Г' => 'rus-g', 'Д' => 'rug-d',
            'Ж' => 'rus-zh', 'З' => 'rus-z',
            'И' => 'rus-i', 'К' => 'rus-k', 'Л' => 'rus-l', 'М' => 'rus-m', 'Н' => 'rus-n', 'О' => 'rus-o', 'П' => 'rus-p',
            'Р' => 'rus-r', 'С' => 'rus-s', 'Т' => 'rus-t', 'У' => 'rus-u', 'Ф' => 'rus-f',
            'Х' => 'rus-h', 'Ц' => 'rus-c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'shch',
            'Э' => 'rus-e', 'Я' => 'rus-ya');

        $codesMenuR = array_flip($codesMenu);

        if ($lGet = $request->query->get('l', null)) {
            if ($l == null) {
                $l = empty($codesMenu[$lGet]) ? null : $codesMenu[$lGet];
                if ($l) {
                    return $this->redirect($this->generateUrl('disease', array('l' => $l)), 301);
                }
            }
        }

        $rusLetter = empty($codesMenuR[$l]) ? null : $codesMenuR[$l];

        $params = array(
            'title' => 'Список болезней по алфавиту',
            'l' => $l,
            'rusLetter' => $rusLetter,
            'q' => $q,
            'menu_drugs' => 'disease',
            'codesMenu' => $codesMenu,
        );

        if ($l) {
            $articles = $em->getRepository('VidalDrugBundle:Article')->findDisease($rusLetter);
            $params['articles'] = $this->highlight($articles, $rusLetter);
        }
        elseif ($q) {
            $q = trim($q);
            $params['articles'] = $em->getRepository('VidalDrugBundle:Article')->findByQuery($q);
        }

        return $params;
    }

    private function highlight($articles, $l)
    {
        foreach ($articles as &$article) {
            # подсвечиваем заголовок статьи
            $words = explode(' ', $article['title']);
            $title = '';
            foreach ($words as $word) {
                $firstLetter = mb_strtoupper(mb_substr($word, 0, 1, 'utf-8'), 'utf-8');
                $title[] = $firstLetter == $l ? '<b>' . $word . '</b>' : $word;
            }
            $article['title'] = implode(' ', $title);

            # подсвечиваем синонимы
            $words = explode(' ', $article['synonym']);
            $title = '';
            foreach ($words as $word) {
                $firstLetter = mb_strtoupper(mb_substr($word, 0, 1, 'utf-8'), 'utf-8');
                $title[] = $firstLetter == $l ? '<b>' . $word . '</b>' : $word;
            }
            $article['synonym'] = implode(' ', $title);
        }

        return $articles;
    }

    /** Получить массив идентификаторов продуктов */
    private function getProductIds($products)
    {
        $productIds = array();

        foreach ($products as $product) {
            $productIds[] = $product['ProductID'];
        }

        return $productIds;
    }

    private function strip($string)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));
        return str_replace(explode(' ', '® ™'), '', $string);
    }
}
