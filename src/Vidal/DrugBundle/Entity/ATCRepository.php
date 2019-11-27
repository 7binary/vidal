<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ATCRepository extends EntityRepository
{
    public function findByLetter($l)
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:ATC a
			WHERE a.ATCCode LIKE :l
			ORDER BY a.ATCCode ASC
		')->setParameter('l', $l . '%')
            ->getResult();
    }

    public function findOneByATCCode($ATCCode)
    {
        return $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalDrugBundle:ATC a
		 	WHERE a = :ATCCode
		')->setParameter('ATCCode', $ATCCode)
            ->getOneOrNullResult();
    }

    public function findByDocumentID($DocumentID)
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:ATC a
			JOIN a.documents d WITH d = :DocumentID
		')->setParameter('DocumentID', $DocumentID)
            ->getResult();
    }

    public function findByProducts($productIds)
    {
        return $this->_em->createQuery('
			SELECT DISTINCT a
			FROM VidalDrugBundle:ATC a
			JOIN a.products p
			WHERE p IN (:productIds)
		')->setParameter('productIds', $productIds)
            ->getResult();
    }

    public function findByQuery($q)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('DISTINCT a.ATCCode, a.RusName, a.EngName, parent.ATCCode as parentATCCode, parent.countProducts as parentCountProducts, parent.RusName as parentRusName, a.countProducts')
            ->from('VidalDrugBundle:ATC', 'a')
            ->leftJoin('a.parent', 'parent')
            ->where('a.ATCCode LIKE :q')
            ->orderBy('a.ATCCode', 'ASC')
            ->setParameter('q', $q . '%');

        # поиск по всем словам вместе
        $words = explode(' ', $q);
        $where = '';

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' AND ';
            }
            $where .= "(a.RusName LIKE '$word%' OR a.EngName LIKE '$word%' OR a.RusName LIKE '% $word%' OR a.EngName LIKE '% $word%')";
        }

        $qb->orWhere($where);
        $atcCodesRaw = $qb->getQuery()->getResult();

        # поиск по одному из слов
        if (empty($atcCodesRaw)) {
            foreach ($words as $word) {
                if (mb_strlen($word, 'utf-8') < 3) {
                    return array();
                }
            }

            $where = '';

            for ($i = 0; $i < count($words); $i++) {
                $word = $words[$i];
                if ($i > 0) {
                    $where .= ' OR ';
                }
                $where .= "(a.RusName LIKE '$word%' OR a.EngName LIKE '$word%' OR a.RusName LIKE '% $word%' OR a.EngName LIKE '% $word%')";
            }

            $qb->where('a.ATCCode LIKE :q');
            $qb->orWhere($where);

            $atcCodesRaw = $qb->getQuery()->getResult();

        }

        $atcCodes = array();

        for ($i = 0, $c = count($atcCodesRaw); $i < $c; $i++) {
            $key = $atcCodesRaw[$i]['ATCCode'];
            $atcCodes[$key] = $atcCodesRaw[$i];
        }

        return $atcCodes;
    }

    public function findChildren($atcCode)
    {
        $children = array($atcCode);
        $pdo = $this->_em->getConnection();
        $stmt = $pdo->prepare("SELECT * from atc WHERE ParentATCCode = :parent ORDER BY ATCCode");

        $stmt->bindParam('parent', $atcCode);
        $stmt->execute();
        $atcCodes1 = $stmt->fetchAll();

        foreach ($atcCodes1 as $atc) {
            $children[] = $atc['ATCCode'];
            $stmt->bindParam('parent', $atc['ATCCode']);
            $stmt->execute();
            $atcCodes2 = $stmt->fetchAll();
            foreach ($atcCodes2 as $atc) {
                $children[] = $atc['ATCCode'];
                $stmt->bindParam('parent', $atc['ATCCode']);
                $stmt->execute();
                $atcCodes3 = $stmt->fetchAll();
                foreach ($atcCodes3 as $atc) {
                    $children[] = $atc['ATCCode'];
                    $stmt->bindParam('parent', $atc['ATCCode']);
                    $stmt->execute();
                    $atcCodes4 = $stmt->fetchAll();
                    foreach ($atcCodes4 as $atc) {
                        $children[] = $atc['ATCCode'];
                        $stmt->bindParam('parent', $atc['ATCCode']);
                        $stmt->execute();
                        $atcCodes5 = $stmt->fetchAll();
                        foreach ($atcCodes5 as $atc) {
                            $children[] = $atc['ATCCode'];
                        }
                    }
                }
            }
        }

        return $children;
    }

    public function findParents($atcCode)
    {
        $parents = array($atcCode);
        $pdo = $this->_em->getConnection();
        $stmt = $pdo->prepare("SELECT * from atc WHERE ATCCode = '$atcCode'");

        $stmt->bindParam('parent', $atcCode);
        $stmt->execute();
        $atc = $stmt->fetchAll();

        if (!empty($atc)) {
            $atc = $atc[0];
            if (!empty($atc['ParentATCCode']) && $atc['Level'] > 3) {
                $parents[] = $atc['ParentATCCode'];
                $stmt = $pdo->prepare("SELECT * from atc WHERE ATCCode = '{$atc['ParentATCCode']}'");
                $stmt->execute();
                $atc1 = $stmt->fetchAll();
                if (!empty($atc1) && !empty($atc1[0]) && !empty($atc1[0]['ParentATCCode']) && $atc1[0]['Level'] > 3) {
                    $parents[] = $atc1[0]['ParentATCCode'];
                    $stmt = $pdo->prepare("SELECT * from atc WHERE ATCCode = '{$atc1[0]['ParentATCCode']}'");
                    $stmt->execute();
                    $atc2 = $stmt->fetchAll();
                    if (!empty($atc2) && !empty($atc2[0]) && !empty($atc2[0]['ParentATCCode']) && $atc2[0]['Level'] > 3) {
                        $parents[] = $atc2[0]['ParentATCCode'];
                    }
                }
            }
        }

        return $parents;
    }

    public function gaChildren($children)
    {
        return $this->_em->createQuery("
            SELECT SUM(p.ga_pageviews) 
            FROM VidalDrugBundle:Product p
            WHERE p.ProductID IN (
                SELECT DISTINCT p2.ProductID
                FROM VidalDrugBundle:Product p2
                JOIN p2.atcCodes a
                LEFT JOIN p2.document d
                WHERE a.ATCCode IN (:children)
                    AND (d.inactive IS NULL OR d.inactive = FALSE)
                    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p2.MarketStatusID IN (1,2,7)
                    AND p2.ProductTypeCode NOT IN ('SUBS')
                    AND p2.inactive = FALSE
                    AND p2.IsNotForSite = FALSE
                    AND p2.parent IS NULL
                    AND p2.MainID IS NULL
            )
        ")->setParameter('children', $children)
            ->getSingleScalarResult();
    }

    public function gaChildrenProducts($children)
    {
        $products = $this->_em->createQuery("
            SELECT p.ga_pageviews, p.ProductID, p.Name, p.url, p.RusName
            FROM VidalDrugBundle:Product p
            WHERE p.ProductID IN (
                SELECT DISTINCT p2.ProductID
                FROM VidalDrugBundle:Product p2
                JOIN p2.atcCodes a
                LEFT JOIN p2.document d
                WHERE a.ATCCode IN (:children)
                    AND (d.inactive IS NULL OR d.inactive = FALSE)
                    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p2.MarketStatusID IN (1,2,7)
                    AND p2.ProductTypeCode NOT IN ('SUBS')
                    AND p2.inactive = FALSE
                    AND p2.IsNotForSite = FALSE
                    AND p2.parent IS NULL
                    AND p2.MainID IS NULL
            )
            ORDER BY p.ga_pageviews DESC
        ")->setParameter('children', $children)
            ->getResult();

        foreach ($products as &$product) {
            $loc = empty($product['url'])
                ? "https://www.vidal.ru/drugs/{$product['Name']}__{$product['ProductID']}"
                : "https://www.vidal.ru/drugs/{$product['url']}";
            $product['loc'] = $loc;
        }

        return $products;
    }

    public function gaCountChildren($children)
    {
        $count = $this->_em->createQuery("
            SELECT COUNT(DISTINCT p.ProductID)
            FROM VidalDrugBundle:Product p
            WHERE p.ProductID IN (
                SELECT DISTINCT p2.ProductID
                FROM VidalDrugBundle:Product p2
                JOIN p2.atcCodes a
                LEFT JOIN p2.document d
                WHERE a.ATCCode IN (:children)
                    AND (d.inactive IS NULL OR d.inactive = FALSE)
                    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p2.MarketStatusID IN (1,2,7)
                    AND p2.ProductTypeCode NOT IN ('SUBS')
                    AND p2.inactive = FALSE
                    AND p2.IsNotForSite = FALSE
                    AND p2.parent IS NULL
                    AND p2.MainID IS NULL
            )
            ORDER BY p.ga_pageviews DESC
        ")->setParameter('children', $children)
            ->getSingleScalarResult();

        return $count;
    }

    public function countProducts()
    {
        return $this->_em->createQuery('
		 	SELECT a.ATCCode, COUNT(p.ProductID) as countProducts
			FROM VidalDrugBundle:Product p
			JOIN p.atcCodes a
			WHERE p.MarketStatusID IN (1,2)
				AND p.ProductTypeCode IN (\'DRUG\',\'GOME\')
				AND p.inactive = FALSE
			GROUP BY a
		')->getResult();
    }

    public function findForTree()
    {
        return $this->_em->createQuery("
			SELECT a.ATCCode id, a.RusName text
			FROM VidalDrugBundle:ATC a
			WHERE a.ParentATCCode = ''
			ORDER BY a.ATCCode ASC
		")->getResult();
    }

    public function jsonForTree()
    {
        $atcRaw = $this->_em->createQuery('
			SELECT a.ATCCode id, a.RusName text, a.ParentATCCode, a.countProducts
			FROM VidalDrugBundle:ATC a
			ORDER BY a.ATCCode ASC
		')->getResult();

        $atc = array();

        for ($i = 0; $i < count($atcRaw); $i++) {
            $key = $atcRaw[$i]['id'];
            $atc[$key] = $atcRaw[$i];
        }

        return $atc;
    }

    public function findAutocomplete()
    {
        $atcCodes = $this->_em->createQuery('
			SELECT a.ATCCode, a.RusName, a.EngName
			FROM VidalDrugBundle:ATC a
		')->getResult();

        $atcNames = array();

        for ($i = 0; $i < count($atcCodes); $i++) {
            $patterns = array('/<SUP>.*<\/SUP>/', '/<SUB>.*<\/SUB>/', '/&alpha;/', '/&amp;/');
            $replacements = array('', '', ' ', ' ', '', '');
            $RusName = preg_replace($patterns, $replacements, $atcCodes[$i]['RusName']);
            $RusName = mb_strtolower(str_replace('  ', ' ', $RusName), 'UTF-8');
            $EngName = preg_replace($patterns, $replacements, $atcCodes[$i]['EngName']);
            $EngName = mb_strtolower(str_replace('  ', ' ', $EngName), 'UTF-8');

            if (!empty($RusName)) {
                $atcNames[] = mb_strtolower($atcCodes[$i]['ATCCode'], 'UTF-8');
            }

            if (!empty($EngName)) {
                $atcNames[] = mb_strtolower($atcCodes[$i]['ATCCode'], 'UTF-8');
            }
        }

        $atcNames = array_unique($atcNames);
        usort($atcNames, 'strcasecmp');

        return $atcNames;
    }

    public function getOptions()
    {
        $raw = $this->_em->createQuery('
			SELECT a.ATCCode, a.RusName, a.EngName
			FROM VidalDrugBundle:ATC a
		 	ORDER BY a.ATCCode ASC
		 ')->getResult();

        $items = array();

        foreach ($raw as $r) {
            $title = $r['ATCCode'] . ' - ' . $r['RusName'];
            if (!empty($r['EngName'])) {
                $title .= ' (' . $r['EngName'] . ')';
            }
            $items[] = array(
                'id' => $r['ATCCode'],
                'title' => $title
            );
        }

        return $items;
    }

    public function getChoices()
    {
        $raw = $this->_em->createQuery('
			SELECT a.ATCCode, a.RusName, a.EngName
			FROM VidalDrugBundle:ATC a
		 	ORDER BY a.ATCCode ASC
		 ')->getResult();

        $items = array();

        foreach ($raw as $r) {
            $key = $r['ATCCode'];
            $title = $r['ATCCode'] . ' - ' . $r['RusName'];
            if (!empty($r['EngName']) && $r['EngName'] != $r['RusName']) {
                $title .= ' (' . $r['EngName'] . ')';
            }
            $items[$key] = $title;
        }

        return $items;
    }

    public function getParent($products)
    {
        $atcCodes = $products->getAtcCodes();

        if (empty($atcCodes)) {
            return null;
        }

        foreach ($atcCodes as $atc) {
            $parentCode = $atc->getParentATCCode();

            if (!empty($parentCode)) {
                $parentAtc = $this->_em->createQuery('
					SELECT a
					FROM VidalDrugBundle:ATC a
					WHERE a.ATCCode = :atc
				')->setParameter('atc', $parentCode)
                    ->getOneOrNullResult();

                if (!empty($parentAtc)) {
                    return $parentAtc;
                }
            }
        }

        return null;
    }

    public function adminAutocomplete($term)
    {
        $atcCodes = $this->_em->createQuery('
			SELECT a.ATCCode, a.RusName
			FROM VidalDrugBundle:ATC a
			WHERE a.ATCCode LIKE :atc
				OR a.RusName LIKE :RusName
			ORDER BY a.ATCCode ASC
		')->setParameter('atc', $term . '%')
            ->setParameter('RusName', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($atcCodes as $atc) {
            $data[] = array(
                'id' => $atc['ATCCode'],
                'text' => $atc['ATCCode'] . ' - ' . $atc['RusName']
            );
        }

        return $data;
    }
}