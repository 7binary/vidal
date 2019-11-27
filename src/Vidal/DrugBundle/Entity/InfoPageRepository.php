<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class InfoPageRepository extends EntityRepository
{
    public function findGa($InfoPageIDs)
    {
        return $this->_em->createQuery('
			SELECT SUM(p.ga_pageviews)
			FROM VidalDrugBundle:Product p
			WHERE p.ProductID IN (
			  SELECT p2.ProductID
			  FROM VidalDrugBundle:Product p2
			  JOIN p2.document d
			  JOIN d.infoPages i
			  WHERE i.InfoPageID IN (:InfoPageIDs)
			    AND (d.inactive IS NULL OR d.inactive = FALSE)
			    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			)
		')->setParameter('InfoPageIDs', $InfoPageIDs)
            ->getSingleScalarResult();
    }

    public function findGaCount($InfoPageIDs)
    {
        return $this->_em->createQuery('
			SELECT COUNT(DISTINCT p.ProductID)
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			JOIN d.infoPages i
			WHERE i.InfoPageID IN (:InfoPageIDs)
			    AND (d.inactive IS NULL OR d.inactive = FALSE)
			    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('InfoPageIDs', $InfoPageIDs)
            ->getSingleScalarResult();
    }

    public function findGrouped()
    {
        $pdo = $this->_em->getConnection();

        $stmt = "
			SELECT InfoPageID, RusName, countProducts
			FROM infopage
			ORDER BY countProducts DESC
		";

        $stmt = $pdo->prepare($stmt);
        $stmt->execute();
        $raw = $stmt->fetchAll();

        $names = array();

        foreach ($raw as $r) {
            $name = mb_strtolower($r['RusName'], 'utf-8');

            if (!isset($names[$name])) {
                $names[$name] = $r;
            }
        }

        return $names;
    }

    public function findOneByInfoPageID($InfoPageID)
    {
        return $this->_em->createQuery("
			SELECT i
			FROM VidalDrugBundle:InfoPage i
			LEFT JOIN VidalDrugBundle:Country c WITH i.CountryCode = c
			WHERE i.CountryEditionCode = 'RUS'
				AND i = :InfoPageID
		")->setParameter('InfoPageID', $InfoPageID)
            ->getOneOrNullResult();
    }

    public function findForExcel()
    {
        return $this->_em->createQuery("
			SELECT i.InfoPageID, i.RusName, c.RusName Country, i.countProducts
			FROM VidalDrugBundle:InfoPage i
			LEFT JOIN VidalDrugBundle:Country c WITH i.CountryCode = c
			WHERE i.CountryEditionCode = 'RUS' AND i.countProducts > 0
			ORDER BY i.RusName ASC
		")->getResult();
    }

    public function findByInfoPageID($InfoPageID)
    {
        return $this->_em->createQuery("
			SELECT i.InfoPageID, i.RusName, i.RusAddress, c.RusName Country, i.photo
			FROM VidalDrugBundle:InfoPage i
			LEFT JOIN VidalDrugBundle:Country c WITH i.CountryCode = c
			WHERE i.CountryEditionCode = 'RUS'
				AND i = :InfoPageID
		")->setParameter('InfoPageID', $InfoPageID)
            ->getOneOrNullResult();
    }

    public function findByProducts($products)
    {
        $results = array();
        $documentIds = array();
        $productIds = array();

        if ($products instanceof Product) {
            /** @var Document $document */
            if ($document = $products->getDocument()) {
                $documentIds = array($document->getDocumentID());
            }
            $productIds = array($products->getProductID());
        }
        else {
            foreach ($products as $product) {
                $key = $product['DocumentID'];
                if (!isset($documentIds[$key])) {
                    $documentIds[$key] = '';
                }
                $key = $product['ProductID'];
                if (!isset($productIds[$key])) {
                    $productIds[$key] = '';
                }
            }
            $documentIds = array_keys($documentIds);
            $productIds = array_keys($productIds);
        }

        $currYear = date('Y');

        if (!empty($productIds)) {
            $byProduct = $byProduct = $this->_em->createQuery("
                SELECT i.InfoPageID, i.RusName, c.RusName Country, p.ProductID, 
                  i.photo, i.logo, i.RusAddress
                FROM VidalDrugBundle:InfoPage i
                INNER JOIN i.products p WITH p IN (:productIds)
                LEFT JOIN VidalDrugBundle:Country c WITH i.CountryCode = c
                WHERE i.CountryEditionCode = 'RUS'
            ")->setParameter('productIds', $productIds)
                ->getResult();

            # надо сгруппировать по ID препарата
            if (!empty($byProduct)) {
                foreach ($byProduct as $infoPage) {
                    $key = $infoPage['ProductID'];
                    $InfoPageID = $infoPage['InfoPageID'];
                    if (!isset($results[$key])) {
                        $results[$key] = array();
                    }
                    $results[$key][$InfoPageID] = $infoPage;
                }
            }
        }

        if (!empty($documentIds)) {
            $byDocument = $this->_em->createQuery("
                SELECT i.InfoPageID, i.RusName, c.RusName Country, d.DocumentID, p.ProductID, 
                  i.photo, i.logo, i.RusAddress
                FROM VidalDrugBundle:InfoPage i
                INNER JOIN i.documents d WITH d IN (:documentIds)
                INNER JOIN d.products p
                LEFT JOIN VidalDrugBundle:Country c WITH i.CountryCode = c
                WHERE i.CountryEditionCode = 'RUS'
                    AND d.IsApproved = 1
                    AND d.DocumentID IN (:documentIds)
                    AND (d.YearEdition IS NULL OR d.YearEdition <= :YearEdition)
                ORDER BY d.YearEdition DESC    
            ")->setParameter('documentIds', $documentIds)
                ->setParameter('YearEdition', $currYear)
                ->getResult();

            # надо сгруппировать по ID препарата
            if (!empty($byDocument)) {
                foreach ($byDocument as $infoPage) {
                    $key = $infoPage['ProductID'];
                    $InfoPageID = $infoPage['InfoPageID'];
                    if (isset($results[$key])) {
                        continue; # выставили уже
                    }
                    $results[$key][$InfoPageID] = $infoPage;
                }
            }
        }

        if (empty($results)) {
            return array();
        }

        foreach ($results as $ProductID => &$data) {
            if ($products instanceof Product) {
                return $this->findPictures($data);
            }
            elseif (!empty($data)) {
                $data = array_values($data);
            }
        }

        return $results;
    }

    private function findPictures($infoPages)
    {
        foreach ($infoPages as $InfoPageID => &$infoPage) {
            $picture = $this->_em->createQuery('
                SELECT p.PathForElectronicEdition path
                FROM VidalDrugBundle:Picture p
                JOIN p.infoPages i WITH i = :InfoPageID
            ')->setParameter('InfoPageID', $InfoPageID)
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $infoPage['picture_path'] = empty($picture)
                ? null
                : strtolower(preg_replace('/.+\\\\JPG\\\\/', '', $picture['path']));
        }

        return $infoPages;
    }

    public function findByDocumentID($DocumentID = null, $ProductID = null)
    {
        if ($DocumentID) {
            $byDocument = $this->_em->createQuery("
                SELECT i.InfoPageID, i.RusName, c.RusName Country
                FROM VidalDrugBundle:InfoPage i
                INNER JOIN i.documents d WITH d = :DocumentID
                LEFT JOIN VidalDrugBundle:Country c WITH i.CountryCode = c
                WHERE i.CountryEditionCode = 'RUS'
            ")->setParameter('DocumentID', $DocumentID)
                ->getResult();
        }

        if ($ProductID) {
            $byProduct = $this->_em->createQuery("
                SELECT i.InfoPageID, i.RusName, c.RusName Country
                FROM VidalDrugBundle:InfoPage i
                INNER JOIN i.products p WITH p = :ProductID
                LEFT JOIN VidalDrugBundle:Country c WITH i.CountryCode = c
                WHERE i.CountryEditionCode = 'RUS'
            ")->setParameter('ProductID', $ProductID)
                ->getResult();
        }

        $infoPages = array();

        if (!empty($byProduct)) {
            foreach ($byProduct as $i) {
                $key = $i['InfoPageID'];
                $infoPages[$key] = $i;
            }
        }

        if (!empty($byDocument)) {
            foreach ($byDocument as $i) {
                $key = $i['InfoPageID'];
                $infoPages[$key] = $i;
            }
        }

        return empty($infoPages) ? null : array_values($infoPages);
    }

    public function getQuery()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('i')
            ->from('VidalDrugBundle:InfoPage', 'i')
            ->orderBy('i.RusName', 'ASC')
            ->where("i.CountryEditionCode = 'RUS'")
            ->andWhere('i.countProducts > 0');

        return $qb->getQuery();
    }

    public function getNames()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('i.RusName')
            ->from('VidalDrugBundle:InfoPage', 'i')
            ->orderBy('i.RusName', 'ASC')
            ->where("i.CountryEditionCode = 'RUS'");

        $results = $qb->getQuery()->getResult();
        $names = array();

        foreach ($results as $result) {
            $name = preg_replace('/ &.+; /', ' ', $result['RusName']);
            $name = preg_replace('/&.+;/', ' ', $name);

            $names[] = $name;
        }

        $uniques = array();

        foreach ($names as $name) {
            if (!isset($uniques[$name])) {
                $uniques[$name] = '';
            }
        }

        return array_keys($uniques);
    }

    public function findByLetter($l)
    {
        return $this->_em->createQuery('
			SELECT i
			FROM VidalDrugBundle:InfoPage i
			JOIN i.documents d
			WHERE i.RusName LIKE :letter
				AND i.countProducts > 0
			ORDER BY i.RusName ASC
		')->setParameter('letter', $l . '%')
            ->getResult();
    }

    public function findByQuery($q)
    {
        $words = explode(' ', $q);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('i')->from('VidalDrugBundle:InfoPage', 'i')->orderBy('i.RusName', 'ASC');

        # поиск по всем словам вместе
        $qb->where('i.countProducts > 0')->andWhere($this->where($words, 'AND'));
        $results = $qb->getQuery()->getResult();

        if (!empty($results)) {
            return $results;
        }

        foreach ($words as $word) {
            if (mb_strlen($word, 'utf-8') < 3) {
                return array();
            }
        }

        # поиск по одному из слов
        $words = $this->getWords($q);
        $qb->where('i.countProducts > 0')->andWhere($this->where($words, 'OR'));
        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findPortfolios($InfoPageID)
    {
        return $this->_em->createQuery('
			SELECT DISTINCT p
			FROM VidalDrugBundle:PharmPortfolio p
			JOIN p.DocumentID d
			JOIN d.infoPages i
			WHERE i.InfoPageID = :InfoPageID
			ORDER BY p.title ASC
		')->setParameter('InfoPageID', $InfoPageID)
            ->getResult();
    }

    private function where($words, $s)
    {
        $s = ($s == 'OR') ? ' OR ' : ' AND ';

        $i = 0;
        $where = '';

        foreach ($words as $word) {
            if ($i > 0) {
                $where .= $s;
            }
            $i++;
            $where .= "(i.RusName LIKE '$word%' OR i.RusName LIKE '% $word%' OR i.EngName LIKE '$word%' OR i.EngName LIKE '% $word%')";
        }

        return $where;
    }

    private function getWords($q)
    {
        $words = explode(' ', $q);
        $isRussian = preg_match('/^[а-яё\s]+$/iu', $q);

        $rus = array(
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'АЙ', 'Й',
            'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
            'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'ай', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
            'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
        );

        $rus2 = array(
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
            'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
            'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
            'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
        );

        $eng = array(
            'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'Y',
            'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
            'H', 'TS', 'CH', 'SH', 'SCH', '', 'Y', '', 'E', 'YU', 'IA',
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y',
            'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
            'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ia',
        );

        if ($isRussian) {
            $words = array_merge($words, explode(' ', str_replace($rus, $eng, $q)));
            $words = array_merge($words, explode(' ', str_replace($rus2, $eng, $q)));
        }
        else {
            $words = array_merge($words, explode(' ', str_replace($eng, $rus, $q)));
            $words = array_merge($words, explode(' ', str_replace($eng, $rus2, $q)));
        }

        return array_unique($words);
    }

    public function findByCompanyName($name)
    {
        return $this->_em->createQuery('
		 	SELECT i
		 	FROM VidalDrugBundle:InfoPage i
		 	WHERE i.RusName LIKE :name
		')->setParameter('name', '%' . $name . '%')
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function adminAutocomplete($term)
    {
        $codes = $this->_em->createQuery('
			SELECT i.InfoPageID, i.RusName
			FROM VidalDrugBundle:InfoPage i
			WHERE i.InfoPageID LIKE :id
				OR i.RusName LIKE :RusName
			ORDER BY i.InfoPageID ASC
		')->setParameter('id', $term . '%')
            ->setParameter('RusName', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($codes as $code) {
            $data[] = array(
                'id' => $code['InfoPageID'],
                'text' => $code['InfoPageID'] . ' - ' . $code['RusName']
            );
        }

        return $data;
    }
}