<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NozologyRepository extends EntityRepository
{
    public function findByCode($code)
    {
        return $this->_em->createQuery('
			SELECT n.NozologyCode, n.Code, n.Name
			FROM VidalDrugBundle:Nozology n
			WHERE n.Code = :code
		')->setParameter('code', $code)
            ->getOneOrNullResult();
    }

    public function findOneByNozologyCode($NozologyCode)
    {
        return $this->_em->createQuery('
			SELECT n
			FROM VidalDrugBundle:Nozology n
			WHERE n.NozologyCode = :NozologyCode
		')->setParameter('NozologyCode', $NozologyCode)
            ->getOneOrNullResult();
    }

    public function findOneByCode($code)
    {
        $code = trim($code, ' ');

        $result = $this->_em->createQuery('
			SELECT n
			FROM VidalDrugBundle:Nozology n
			WHERE n.Code = :code
		')->setMaxResults(1)->setParameter('code', $code)
            ->getOneOrNullResult();

        if (!$result) {
            $result = $this->_em->createQuery('
				SELECT n
				FROM VidalDrugBundle:Nozology n
				WHERE n.NozologyCode = :code
			')->setMaxResults(1)->setParameter('code', $code)
                ->getOneOrNullResult();
        }

        return $result;
    }

    public function findByLetter($l)
    {
        return $this->_em->createQuery('
		 	SELECT n
		 	FROM VidalDrugBundle:Nozology n
		 	WHERE n.NozologyCode LIKE :l
		 ')->setParameter('l', $l . '%')
            ->getResult();
    }

    public function findNozologyNames()
    {
        $names = array();

        $namesRaw = $this->_em->createQuery('
			SELECT n.Name
			FROM VidalDrugBundle:Nozology n
			ORDER BY n.Name ASC
		')->getResult();

        for ($i = 0, $c = count($namesRaw); $i < $c; $i++) {
            $names[] = mb_strtolower($namesRaw[$i]['Name'], 'UTF-8');
        }

        return $names;
    }

    public function findByQuery($q)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('DISTINCT n.Code, n.Name')
            ->from('VidalDrugBundle:Nozology', 'n')
            ->orderBy('n.Name', 'ASC');

        # поиск по словам
        $where = '';
        $words = explode(' ', $q);

        # находим все слова
        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' AND ';
            }
            $where .= "(n.Name LIKE '$word%' OR n.Name LIKE '% $word%' OR n.Code = '$word')";
        }

        $qb->where($where)->andWhere('n.countProducts > 0');
        $nozologies = $qb->getQuery()->getResult();

        # находим какое-либо из слов, если нет результата
        if (empty($nozologies)) {
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
                $where .= "(n.Name LIKE '$word%' OR n.Name LIKE '% $word%')";
            }

            $qb->where($where)->andWhere('n.countProducts > 0');
            $nozologies = $qb->getQuery()->getResult();
        }

        for ($i = 0, $c = count($nozologies); $i < $c; $i++) {
            $nozologies[$i]['Name'] = preg_replace('/' . $q . '/iu', '<span class="query">$0</span>', $nozologies[$i]['Name']);
        }

        return $nozologies;
    }

    public function findByDocumentId($DocumentID)
    {
        return $this->_em->createQuery('
			SELECT DISTINCT n.NozologyCode, n.Code, n.Name
			FROM VidalDrugBundle:Nozology n
			JOIN n.documents d WITH d = :DocumentID
			ORDER BY n.Name ASC
		')->setParameter('DocumentID', $DocumentID)
            ->getResult();
    }

    public function findByCodes($nozologyCodes)
    {
        return $this->_em->createQuery('
		 	SELECT DISTINCT n.NozologyCode, n.Name
		 	FROM VidalDrugBundle:Nozology n
		 	WHERE n.NozologyCode IN (:nozologyCodes)
		')->setParameter('nozologyCodes', $nozologyCodes)
            ->getResult();
    }

    public function findForTree()
    {
        return $this->_em->createQuery('
			SELECT n.Code id, n.Name text
			FROM VidalDrugBundle:Nozology n
			WHERE n.Level = 0
			ORDER BY n.NozologyCode
		')->getResult();
    }

    public function countProducts()
    {
        return $this->_em->createQuery('
			SELECT COUNT(DISTINCT p.ProductID) as countProducts, n.Code
			FROM VidalDrugBundle:Product p
			JOIN p.document d
			JOIN d.nozologies n
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode IN (\'DRUG\',\'GOME\')
				AND p.inactive = FALSE
			GROUP BY n.Code
		')->getResult();
    }

    public function jsonForTree()
    {
        $raw = $this->_em->createQuery('
			SELECT n.Code id, n.Name text, n.Level, n.countProducts, n.NozologyCode nc
			FROM VidalDrugBundle:Nozology n
			ORDER BY n.NozologyCode2
		')->getResult();

        $nozologies = array();

        foreach ($raw as $nozology) {
            $key = $nozology['id'];
            $nozologies[$key] = $nozology;
        }

        return $nozologies;
    }

    public function getOptions()
    {
        $raw = $this->_em->createQuery('
			SELECT n.NozologyCode, n.Name
			FROM VidalDrugBundle:Nozology n
		 	ORDER BY n.NozologyCode ASC
		 ')->getResult();

        $items = array();

        foreach ($raw as $r) {
            $items[] = array(
                'id' => $r['NozologyCode'],
                'title' => $r['NozologyCode'] . ' - ' . $r['Name']
            );
        }

        return $items;
    }

    public function getChoices()
    {
        $raw = $this->_em->createQuery('
			SELECT n.NozologyCode, n.Name
			FROM VidalDrugBundle:Nozology n
		 	ORDER BY n.NozologyCode ASC
		 ')->getResult();

        $items = array();

        foreach ($raw as $r) {
            $key = $r['NozologyCode'];
            $items[$key] = $r['NozologyCode'] . ' - ' . $r['Name'];
        }

        return $items;
    }

    public function adminAutocomplete($term)
    {
        $codes = $this->_em->createQuery('
			SELECT n.NozologyCode, n.Code, n.Name
			FROM VidalDrugBundle:Nozology n
			WHERE n.Code LIKE :code
				OR n.Name LIKE :name
			ORDER BY n.NozologyCode ASC
		')->setParameter('code', $term . '%')
            ->setParameter('name', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($codes as $code) {
            $data[] = array(
                'id' => $code['NozologyCode'],
                'text' => $code['Code'] . ' - ' . $code['Name']
            );
        }

        return $data;
    }

    public function findChildren($code)
    {
        $pdo = $this->_em->getConnection();
        $children = array($code);

        $stmt = $pdo->prepare("SELECT * from nozology WHERE ParentNozologyCode = :parent ORDER BY NozologyCode");
        $stmt->bindParam('parent', $code);
        $stmt->execute();
        $NozologyCodes1 = $stmt->fetchAll();

        foreach ($NozologyCodes1 as $nosology) {
            $children[] = $nosology['NozologyCode'];
            $stmt->bindParam('parent', $nosology['NozologyCode']);
            $stmt->execute();
            $NozologyCodes2 = $stmt->fetchAll();
            foreach ($NozologyCodes2 as $nosology) {
                $children[] = $nosology['NozologyCode'];
                $stmt->bindParam('parent', $nosology['NozologyCode']);
                $stmt->execute();
                $NozologyCodes3 = $stmt->fetchAll();
                foreach ($NozologyCodes3 as $nosology) {
                    $children[] = $nosology['NozologyCode'];
                    $stmt->bindParam('parent', $nosology['NozologyCode']);
                    $stmt->execute();
                    $NozologyCodes4 = $stmt->fetchAll();
                    foreach ($NozologyCodes4 as $nosology) {
                        $children[] = $nosology['NozologyCode'];
                        $stmt->bindParam('parent', $nosology['NozologyCode']);
                        $stmt->execute();
                        $NozologyCodes5 = $stmt->fetchAll();
                        foreach ($NozologyCodes5 as $nosology) {
                            $children[] = $nosology['NozologyCode'];
                        }
                    }
                }
            }
        }

        return $children;
    }

    public function gaChildrenProducts($children)
    {
        $products = $this->_em->createQuery("
            SELECT p.ga_pageviews, p.ga_pageviews, p.ProductID, p.Name, p.url, p.RusName
            FROM VidalDrugBundle:Product p
            WHERE p.ProductID IN (
                SELECT DISTINCT p2.ProductID
                FROM VidalDrugBundle:Product p2
                LEFT JOIN p2.document d
                JOIN d.nozologies n
                WHERE n.NozologyCode IN (:children)
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

    public function gaChildren($children)
    {
        return $this->_em->createQuery("
            SELECT SUM(p.ga_pageviews) 
            FROM VidalDrugBundle:Product p
            WHERE p.ProductID IN (
                SELECT DISTINCT p2.ProductID
                FROM VidalDrugBundle:Product p2
                LEFT JOIN p2.document d
                JOIN d.nozologies n
                WHERE n.NozologyCode IN (:children)
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

    public function gaCountChildren($children)
    {
        return $this->_em->createQuery("
            SELECT COUNT(DISTINCT p.ProductID) 
            FROM VidalDrugBundle:Product p
            JOIN p.document d
            JOIN d.nozologies n
            WHERE n.NozologyCode IN (:children)
              AND p.parent IS NULL
              AND p.MainID IS NULL
        ")->setParameter('children', $children)
            ->getSingleScalarResult();
    }
}