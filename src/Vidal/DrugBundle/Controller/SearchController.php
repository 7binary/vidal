<?php

namespace Vidal\DrugBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Vidal\MainBundle\Entity\Search;
use Vidal\DrugBundle\Entity\Company;
use Vidal\DrugBundle\Entity\InfoPage;
use Vidal\DrugBundle\Entity\Molecule;
use Vidal\DrugBundle\Entity\ATC;
use Vidal\DrugBundle\Entity\Article;
use Vidal\DrugBundle\Entity\Product;
use Vidal\DrugBundle\Entity\Nozology;
use Vidal\DrugBundle\Entity\Document;
use Vidal\DrugBundle\Entity\Contraindication;
use Vidal\DrugBundle\Entity\ClPhGroups;
use Vidal\DrugBundle\Entity\PhThGroups;

class SearchController extends Controller
{
	const PRODUCTS_PER_PAGE = 40;

	/**
	 * @Route("/search", name="search", options={"expose":true})
	 *
	 * @Template("VidalDrugBundle:Search:search.html.twig")
	 */
	public function searchAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager('drug');

		$q = $request->query->get('q', ''); # поисковый запрос
		$q = trim($q);
		$o = $request->query->get('o', ''); # поисковый запрос до транслитерации
		$o = trim($o);
		$t = $request->query->get('t', 'all'); # тип запроса из селект-бокса
		$p = $request->query->get('p', 1); # номер страницы
		$bad = $request->query->has('bad');
		$referer = $request->headers->get('referer');

		$params = array(
			'q' => $q,
			'o' => $o,
			't' => $t,
			'p' => $p,
			'title' => 'Поиск',
		);

		if ($p > 1) {
		    $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

		$search = new Search();
		$search->setQuery($q);
		$search->setReferer($referer);

		# поисковый запрос не может быть меньше 2
		if (mb_strlen($q, 'UTF-8') < 2) {
			$search->setTooShort(true);
			// $emMain->persist($search);
			// $emMain->flush();

			return $this->render('VidalDrugBundle:Search:search_too_short.html.twig', $params);
		}

		if ($t == 'all' || $t == 'product') {
			list($productsRaw, $anyOfWord) = $em->getRepository(Product::class)->findByQuery($q, $bad);

			# если включаем бады, то их надо в отдельную группу
			if ($bad && $p == 1) {
				$products = array();
				$bads = array();
				$mis = array();
				$pits = array();
				$cosms = array();
				$paras = array();

				foreach ($productsRaw as $product) {
					switch ($product['ProductTypeCode']) {
						case Product::TYPE_BAD:
							$bads[] = $product;
							break;
						case Product::TYPE_MI:
							$mis[] = $product;
							break;
						case Product::TYPE_NUTR:
							$pits[] = $product;
							break;
						case Product::TYPE_COSM:
							$cosms[] = $product;
							break;
						case Product::TYPE_PARA:
							$paras[] = $product;
							break;
						default:
							$products[] = $product;
					}
				}

                if (count($cosms))
                {
                    $cosmIds = $this->getProductIds($cosms);
                    $params['cosms'] = $cosms;
                    $params['cosmCompanies'] = $em->getRepository(Company::class)->findByProducts($cosmIds);
                    $params['cosmInfoPages'] = $em->getRepository(InfoPage::class)->findByProducts($cosms);
                }

                if (count($paras))
                {
                    $paraIds = $this->getProductIds($paras);
                    $params['paras'] = $paras;
                    $params['paraCompanies'] = $em->getRepository(Company::class)->findByProducts($paraIds);
                    $params['paraInfoPages'] = $em->getRepository(InfoPage::class)->findByProducts($paras);
                }

                if (count($pits))
                {
                    $pitIds = $this->getProductIds($pits);
                    $params['pits'] = $pits;
                    $params['pitCompanies'] = $em->getRepository(Company::class)->findByProducts($pitIds);
                    $params['pitInfoPages'] = $em->getRepository(InfoPage::class)->findByProducts($pits);
                }

                if (count($pits))
                {
                    $pitIds = $this->getProductIds($pits);
                    $params['pits'] = $pits;
                    $params['pitCompanies'] = $em->getRepository(Company::class)->findByProducts($pitIds);
                    $params['pitInfoPages'] = $em->getRepository(InfoPage::class)->findByProducts($pits);
                }

				if (count($bads)) {
					$badIds = $this->getProductIds($bads);
					$params['bads'] = $bads;
					$params['bad_companies'] = $em->getRepository(Company::class)->findByProducts($badIds);
					$params['bad_infoPages'] = $em->getRepository(InfoPage::class)->findByProducts($bads);
				}

				if (count($mis)) {
					$miIds = $this->getProductIds($mis);
					$params['mis'] = $mis;
					$params['mi_companies'] = $em->getRepository(Company::class)->findByProducts($miIds);
					$params['mi_infoPages'] = $em->getRepository(InfoPage::class)->findByProducts($mis);
				}
			}
			else {
				$products = $productsRaw;
			}

			$paginator = $this->get('knp_paginator');
			$pagination = $paginator->paginate($products, $p, self::PRODUCTS_PER_PAGE);
			$params['productsPagination'] = $pagination;
			$params['anyOfWord'] = $anyOfWord;

			if ($pagination->getTotalItemCount()) {
				$productIds = $this->getProductIds($pagination);
				$params['companies'] = $em->getRepository(Company::class)->findByProducts($productIds);
				$params['infoPages'] = $em->getRepository(InfoPage::class)->findByProducts($pagination);
			}
		}

		if ($p == 1) {
			# поиск по активному веществу
			if ($t == 'all' || $t == 'molecule') {
				$params['molecules'] = $em->getRepository(Molecule::class)->findByQuery($q);
			}

			# поиск по АТХ коду
			if ($t == 'all' || $t == 'atc') {
				$qUpper = mb_strtoupper($q, 'utf-8');
				$params['atcCodes'] = $em->getRepository(ATC::class)->findByQuery($qUpper);
			}

			# поиск по компании
			if ($t == 'all' || $t == 'company') {
				$params['search_companies'] = $em->getRepository(Company::class)->findByQuery($q);
				$params['search_infoPages'] = $em->getRepository(InfoPage::class)->findByQuery($q);
			}

			# поиск по заболеванию (это статьи и синонимы)
			if ($t == 'all' || $t == 'disease') {
				$articles = $em->getRepository(Article::class)->findByQuery($q);
				# если есть БАДы, то исключаем дублирующие их статьи
				if (isset($params['bads']) && !empty($articles)) {
					$articles = $this->excludeBads($articles, $params['bads']);
				}
				$params['articles'] = $articles;
			}
		}

		if (empty($params['bads'])
			&& empty($params['mis'])
			&& empty($params['companies'])
			&& empty($params['infoPages'])
			&& empty($params['molecules'])
			&& empty($params['atcCodes'])
			&& empty($params['articles'])
			&& empty($params['search_companies'])
			&& empty($params['search_infoPages'])
		) {
			if (preg_match("/[a-z\\[\\]\\;\\'\\,\\.\\ ]+/", $q) && !$request->query->get('redirected', null)) {
				return $this->redirect($this->generateUrl('search', array(
					'q' => $this->modifySearchQuery($q),
					'o' => $q,
					'redirected' => 1,
				)));
			}
			else {
				$search->setWithoutResults(true);
			}
		}

		// $emMain->persist($search);
		// $emMain->flush();

		return $params;
	}

	/**
	 * @Route("/searche")
	 * @Route("/drugs/search", name="searche", options={"expose"=true})
	 */
	public function redirectSearche()
	{
		return $this->redirect($this->generateUrl('drugs'), 301);
	}

	/**
	 * @Route("/drugs", name="drugs")
     * @Route("/drugs/main", name="drugs_main")
	 *
	 * @Template("VidalDrugBundle:Search:searche.html.twig")
	 */
	public function searcheAction(Request $request)
	{
        $routeName = $request->get('_route');
        if ($routeName == 'drugs_main') {
            return $this->redirect($this->generateUrl('drugs'), 301);
        }

		$em = $this->getDoctrine()->getManager('drug');
		$q = $request->query->get('q', ''); # поисковый запрос
		$q = trim($q);
		$t = $request->query->get('t', 'all'); # тип запроса из селект-бокса
		$p = $request->query->get('p', 1); # номер страницы
		$bad = $request->query->has('bad'); # включать ли бады
		$o = $request->query->get('o', null); # опция на поиск по группе из списка

		$params = array(
			'q' => $q,
			't' => $t,
			'o' => $o,
			'p' => $p,
			'title' => 'Расширенный поиск',
			'menu_drugs' => 'searche',
		);

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

		# поисковый запрос не может быть меньше 2
		if (empty($q)) {
			return $params;
		}
		elseif (mb_strlen($q, 'UTF-8') < 2) {
			return $this->render('VidalDrugBundle:Search:searche_too_short.html.twig', $params);
		}

		if ($t == 'all' || $t == 'product') {
			list($productsRaw, $anyOfWord) = $em->getRepository(Product::class)->findByQuery($q, $bad);
			# если включаем бады, то их надо в отдельную группу
			if ($bad && $p == 1) {
				$products = array();
				$bads = array();
				$mis = array();

				foreach ($productsRaw as $product) {
					switch ($product['ProductTypeCode']) {
						case Product::TYPE_BAD:
							$bads[] = $product;
							break;
						case Product::TYPE_MI:
							$mis[] = $product;
							break;
						default:
							$products[] = $product;
					}
				}

				if (count($bads)) {
					$badIds = $this->getProductIds($bads);
					$params['bads'] = $bads;
					$params['bad_companies'] = $em->getRepository(Company::class)->findByProducts($badIds);
					$params['bad_infoPages'] = $em->getRepository(InfoPage::class)->findByProducts($bads);
				}

				if (count($mis)) {
					$miIds = $this->getProductIds($mis);
					$params['mis'] = $mis;
					$params['mi_companies'] = $em->getRepository(Company::class)->findByProducts($miIds);
					$params['mi_infoPages'] = $em->getRepository(InfoPage::class)->findByProducts($mis);
				}
			}
			else {
				$products = $productsRaw;
			}

			$paginator = $this->get('knp_paginator');
			$pagination = $paginator->paginate($products, $p, self::PRODUCTS_PER_PAGE);

			$params['productsPagination'] = $pagination;
			$params['anyOfWord'] = $anyOfWord;

			if ($pagination->getTotalItemCount()) {
				$productIds = $this->getProductIds($pagination);
				$params['companies'] = $em->getRepository(Company::class)->findByProducts($productIds);
				$params['infoPages'] = $em->getRepository(InfoPage::class)->findByProducts($pagination);
			}
		}

		# на следующих страницах отображаются только препараты
		if ($p == 1) {
			# поиск по активному веществу
			if ($t == 'all' || $t == 'molecule') {
				$params['molecules'] = $em->getRepository(Molecule::class)->findByQuery($q);
			}

			# поиск по показаниям (МКБ-10) - Nozology
			if ($t == 'all' || $t == 'nosology') {
				$params['nozologies'] = $em->getRepository(Nozology::class)->findByQuery($q);
			}

			# поиск по АТХ коду
			if ($t == 'all' || $t == 'atc') {
				$qUpper = mb_strtoupper($q, 'utf-8');
				$params['atcCodes'] = $em->getRepository(ATC::class)->findByQuery($qUpper);
				$params['atcTree'] = true;
			}

			# поиск по компании
			if ($t == 'all' || $t == 'company') {
				$params['search_companies'] = $em->getRepository(Company::class)->findByQuery($q);
				$params['search_infoPages'] = $em->getRepository(InfoPage::class)->findByQuery($q);
			}

			# поиск по заболеванию (это статьи и синонимы)
			if ($t == 'all' || $t == 'disease') {
				$articles = $em->getRepository(Article::class)->findByQuery($q);
				# если есть БАДы, то исключаем дублирующие их статьи
				if (isset($params['bads']) && !empty($articles)) {
					$articles = $this->excludeBads($articles, $params['bads']);
				}
				$params['articles'] = $articles;
			}

			# поиск по клиннико-фармакологической группе
			if ($t == 'all' || $t == 'clphgroup') {
				$params['clphgroups'] = $em->getRepository(Document::class)->findClPhGroupsByQuery($q);
			}

			# поиск по фармако-терапевтической группе
			if ($t == 'all' || $t == 'phthgroup') {
				$params['phthgroups'] = $em->getRepository(Product::class)->findPhThGroupsByQuery($q);
			}
		}

		if (empty($params['bads'])
			&& empty($params['mis'])
			&& empty($params['companies'])
			&& empty($params['infoPages'])
			&& empty($params['molecules'])
			&& empty($params['atcCodes'])
			&& empty($params['articles'])
			&& empty($params['search_companies'])
			&& empty($params['search_infoPages'])
		) {
			if (preg_match("/[a-z\\[\\]\\;\\'\\,\\.\\ ]+/", $q) && !$request->query->get('redirected', null)) {
				return $this->redirect($this->generateUrl('drugs', array(
					'q' => $this->modifySearchQuery($q),
					'o' => $q,
					'redirected' => 1,
				)));
			}
		}

		return $params;
	}

	/**
	 * @Route("/searche/indic")
	 */
	public function indicRedirectAction()
	{
		return $this->redirect($this->generateUrl('drugs'), 301);
	}

	/**
	 * @Route("/search/disease")
	 */
	public function diseaseRedirectAction()
	{
		return $this->redirect($this->generateUrl('drugs'), 301);
	}

	/**
	 * @Template("VidalDrugBundle:Drug:drugs_indic.html.twig")
	 */
	public function searcheIndicAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager('drug');
		$contraCodes = $nozologyCodes = null;
		$params = array(
			'title' => 'Поиск по показаниям/противопоказаниям',
		);

		if ($request->query->has('nozology')) {
			$nozologyCodes = explode('-', $request->query->get('nozology'));

			if (empty($nozologyCodes)) {
				$params['noNozology'] = true;

				return $params;
			}

			$params['nozologies'] = $em->getRepository(Nozology::class)->findByCodes($nozologyCodes);

			if ($request->query->has('contra')) {
				$contraCodes = explode('-', $request->query->get('contra'));
				$params['contraindications'] = $em->getRepository(Contraindication::class)->findByCodes($contraCodes);
			}

			$documentIds = $em->getRepository(Document::class)
				->findIdsByNozologyContraCodes($nozologyCodes, $contraCodes);

			if (!empty($documentIds)) {
				$products = $em
					->getRepository(Product::class)
					->findByDocumentIDs($documentIds);

				if (!empty($products)) {
					$pagination = $this->get('knp_paginator')->paginate(
						$products,
						$request->query->get('p', 1),
						self::PRODUCTS_PER_PAGE
					);

					$productIds = $this->getProductIds($products);

					$params['productsPagination'] = $pagination;
					$params['companies'] = $em->getRepository(Company::class)->findByProducts($productIds);
				}
			}
		}

		return $params;
	}

	public function searcheLetterAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager('drug');
		$t = $request->query->get('t', 'p'); // тип препараты-бады-вместе
		$p = $request->query->get('p', 1); // номер страницы
		$l = $request->query->get('l', null); // буква
		$n = $request->query->has('n'); // только безрецептурные препараты

		$letters = explode(' ', 'А Б В Г Д Е Ж З И Й К Л М Н О П Р С Т У Ф Х Ц Ч Ш Э Ю Я');

		$params = array(
			't' => $t,
			'p' => $p,
			'l' => $l,
			'n' => $n,
			'menu' => 'drugs',
			'title' => 'Поиск по алфавиту',
			'letters' => $letters,
		);

        if ($p > 1) {
            $params['extra_title'] = ' - страница ' . $p;
            $params['extra_description'] = ' Страница ' . $p . '.';
        }

		# БАДы только безрецептурные
		if ($t == 'b') {
			$n = false;
		}

		if ($l != null) {
			$paginator = $this->get('knp_paginator');
			$pagination = $paginator->paginate(
				$em->getRepository(Product::class)->getQueryByLetter($l, $t, $n),
				$p,
				self::PRODUCTS_PER_PAGE
			);

			$products = $pagination->getItems();
			$params['pagination'] = $pagination;

			if (!empty($products)) {
				$productIds = array();

				foreach ($products as $product) {
					$productIds[] = $product->getProductID();
				}

				$params['products'] = $products;
				$params['indications'] = $em->getRepository(Document::class)->findIndicationsByProductIds($productIds);
				$params['companies'] = $em->getRepository(Company::class)->findByProducts($productIds);
			}
		}

		return $params;
	}

	/**
	 * @Route("/search/google", name="searche_google")
	 * @Template("VidalDrugBundle:Search:searche_google.html.twig")
	 */
	public function googleAction()
	{
		$params = array(
			'title' => 'Поиск Google по открытым разделам сайта',
			'menu_drugs' => 'google',
		);

		return $params;
	}

	/**
	 * @Route("/search-options/{type}", name="search_options", options={"expose"=true})
	 */
	public function searchOptions($type)
	{
		$em = $this->getDoctrine()->getManager('drug');

		switch ($type) {
			case 'molecule':
				return new JsonResponse($em->getRepository(Molecule::class)->getOptions());
			case 'atc':
				return new JsonResponse($em->getRepository(ATC::class)->getOptions());
			case 'nosology':
				return new JsonResponse($em->getRepository(Nozology::class)->getOptions());
			case 'clphgroup':
				return new JsonResponse($em->getRepository(ClPhGroups::class)->getOptions());
			case 'phthgroup':
				return new JsonResponse($em->getRepository(PhThGroups::class)->getOptions());
			default:
				return new JsonResponse(array());
		}
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

	private function excludeBads($articlesRaw, $bads)
	{
		$badNames = array();
		$articles = array();

		foreach ($bads as $bad) {
			$badNames[] = $this->stripLower($bad['RusName']);
		}

		foreach ($articlesRaw as $article) {
			$title = $this->stripLower($article['title']);

			if (!in_array($title, $badNames)) {
				$articles[] = $article;
			}
		}

		return $articles;
	}

	private function stripLower($string)
	{
		$string = mb_strtolower(strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8')), 'utf-8');
		return str_replace(explode(' ', '® ™'), '', $string);
	}

	private function modifySearchQuery($query)
	{
		$eng = explode(' ', "q w e r t y u i o p [ ] a s d f g h j k l ; ' z x c v b n m , .");
		$rus = explode(' ', 'й ц у к е н г ш щ з х ъ ф ы в а п р о л д ж э я ч с м и т ь б ю');

		return str_replace($eng, $rus, $query);
	}
}
