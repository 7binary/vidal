<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository
{
    public function findGa($CompanyIDs)
    {
        return $this->_em->createQuery("
			SELECT SUM(p.ga_pageviews)
			FROM VidalDrugBundle:Product p
			WHERE p.ProductID IN (
			    SELECT DISTINCT p2.ProductID
			    FROM VidalDrugBundle:Product p2
			    LEFT JOIN p2.document d
			    JOIN VidalDrugBundle:ProductCompany pc WITH pc.ProductID = p2
			    JOIN VidalDrugBundle:Company c WITH pc.CompanyID = c
			    WHERE c IN (:CompanyIDs)
			        AND (d.inactive IS NULL OR d.inactive = FALSE)
			        AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p.MarketStatusID IN (1,2,7)
                    AND p.ProductTypeCode NOT IN ('SUBS')
                    AND p.inactive = FALSE
                    AND p.IsNotForSite = FALSE
                    AND p.parent IS NULL
                    AND p.MainID IS NULL
			)
		")->setParameter('CompanyIDs', $CompanyIDs)
            ->getSingleScalarResult();
    }

    public function findGaCount($CompanyIDs)
    {
        return $this->_em->createQuery("
			SELECT COUNT(DISTINCT p.ProductID)
			FROM VidalDrugBundle:Product p
			JOIN VidalDrugBundle:ProductCompany pc WITH pc.ProductID = p
			JOIN VidalDrugBundle:Company c WITH pc.CompanyID = c
			WHERE c IN (:CompanyIDs)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->setParameter('CompanyIDs', $CompanyIDs)
            ->getSingleScalarResult();
    }

    public function findGrouped()
    {
        $pdo = $this->_em->getConnection();

        $stmt = "
			SELECT CompanyID, LocalName, countProducts
			FROM company
			WHERE inactive = 0
			ORDER BY countProducts DESC
		";

        $stmt = $pdo->prepare($stmt);
        $stmt->execute();
        $raw = $stmt->fetchAll();

        $names = array();

        foreach ($raw as $r) {
            $name = mb_strtolower($r['LocalName'], 'utf-8');

            if (!isset($names[$name])) {
                $names[$name] = $r;
            }
        }

        return $names;
    }

    public function findOneByCompanyID($CompanyID)
    {
        return $this->_em->createQuery('
			SELECT c
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			WHERE c = :CompanyID AND c.inactive = FALSE
		')->setParameter('CompanyID', $CompanyID)
            ->getOneOrNullResult();
    }

    public function findByCompanyID($CompanyID)
    {
        return $this->_em->createQuery('
			SELECT c.CompanyID, c.LocalName CompanyName, c.Property, country.RusName Country
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			WHERE c = :CompanyID AND c.inactive = FALSE
		')->setParameter('CompanyID', $CompanyID)
            ->getOneOrNullResult();
    }

    public function findOwnersByProducts($productIds)
    {
        $submainProducts = $this->_em->createQuery("
			SELECT p.ProductID
			FROM VidalDrugBundle:Product p
			WHERE (p.MainID IN (:productIds) OR p.ParentID IN (:productIds))
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
		")->setParameter('productIds', $productIds)
            ->getResult();

        if (!empty($submainProducts)) {
            foreach ($submainProducts as $product) {
                $productIds[] = $product['ProductID'];
            }
        }

        $owners = $this->_em->createQuery('
			SELECT DISTINCT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property,
				country.RusName Country
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			WHERE pc.ProductID IN (:productIds)
				AND pc.ItsMainCompany = 1
				AND c.inactive = FALSE
		')->setParameter('productIds', $productIds)
            ->getResult();

        return $owners;
    }

    public function findDistributorsByProducts($productIds)
    {
        $submainProducts = $this->_em->createQuery("
			SELECT p.ProductID
			FROM VidalDrugBundle:Product p
			WHERE (p.MainID IN (:productIds) OR p.ParentID IN (:productIds))
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
		")->setParameter('productIds', $productIds)
            ->getResult();

        if (count($submainProducts)) {
            foreach ($submainProducts as $p) {
                $productIds[] = $p['ProductID'];
            }
        }

        return $this->_em->createQuery('
			SELECT DISTINCT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property,
				country.RusName Country, pc.ItsMainCompany, pc.Ranking
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			WHERE pc.ProductID IN (:productIds)
				AND pc.ItsMainCompany = 0
				AND c.inactive = FALSE
			ORDER BY pc.Ranking ASC
		')->setParameter('productIds', $productIds)
            ->getResult();
    }

    public function findByProducts($productIds)
    {
        $submainProducts = $this->_em->createQuery("
			SELECT p.ProductID
			FROM VidalDrugBundle:Product p
			WHERE (p.MainID IN (:productIds) OR p.ParentID IN (:productIds))
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
		")->setParameter('productIds', $productIds)
            ->getResult();

        if (count($submainProducts)) {
            foreach ($submainProducts as $p) {
                $productIds[] = $p['ProductID'];
            }
        }

        $companies = $this->_em->createQuery('
			SELECT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property,
				country.RusName Country, pc.ItsMainCompany, p.ProductID, p.ParentID, p.MainID
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			LEFT JOIN VidalDrugBundle:Product p WITH p = pc.ProductID
			WHERE pc.ProductID IN (:productIds) AND c.inactive = FALSE
			ORDER BY pc.ItsMainCompany DESC, pc.Ranking ASC
		')->setParameter('productIds', $productIds)
            ->getResult();

        $productCompanies = array();
        $uniques = array();

        # надо получить компании и сгруппировать их по продукту
        foreach ($companies as $company) {
            if (!empty($company['ParentID'])) {
                $key = $company['ParentID'];
            }
            elseif (!empty($company['MainID'])) {
                $key = $company['MainID'];
            }
            else {
                $key = $company['ProductID'];
            }

            $uniq = $key . '_' . $company['CompanyID'] . '_' . $company['CompanyRusNote'];
            if (isset($uniques[$uniq])) {
                continue;
            }
            else {
                $uniques[$uniq] = true;
            }

            isset($productCompanies[$key])
                ? $productCompanies[$key][] = $company
                : $productCompanies[$key] = array($company);
        }

        return $productCompanies;
    }

    public function getQuery()
    {
        return $this->_em->createQuery("
			SELECT c
			FROM VidalDrugBundle:Company c
			WHERE c.CountryEditionCode = 'RUS'
				AND c.countProducts > 0
				AND c.inactive = FALSE
			ORDER BY c.LocalName ASC
		");
    }

    public function findForExcel()
    {
        return $this->_em->createQuery("
			SELECT c.CompanyID, c.LocalName, c.GDDBName, c.Property, c.countProducts, country.RusName Country
			FROM VidalDrugBundle:Company c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			WHERE c.CountryEditionCode = 'RUS' AND c.countProducts > 0 AND c.inactive = FALSE
			ORDER BY c.LocalName ASC
		")->getResult();
    }

    public function findByLetter($l)
    {
        return $this->_em->createQuery("
			SELECT c
			FROM VidalDrugBundle:Company c
			WHERE c.CountryEditionCode = 'RUS'
				AND c.LocalName LIKE :l
				AND c.countProducts > 0
				AND c.inactive = FALSE
			ORDER BY c.LocalName ASC
		")->setParameter('l', $l . '%')
            ->getResult();
    }

    public function findByQueryString($q)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('c')
            ->from('VidalDrugBundle:Company', 'c')
            ->orderBy('c.LocalName', 'ASC')
            ->where("c.CountryEditionCode = 'RUS'")
            ->andWhere("c.countProducts > 0")
            ->andWhere("c.inactive = 0");

        # поиск по всем словам
        $where = '';
        $words = explode(' ', $q);

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' AND ';
            }
            $where .= "(c.LocalName LIKE '$word%' OR c.LocalName LIKE '% $word%')";
        }

        $qb->andWhere($where);
        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function getNames()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('c.LocalName')
            ->from('VidalDrugBundle:Company', 'c')
            ->orderBy('c.LocalName', 'ASC')
            ->where("c.CountryEditionCode = 'RUS'");

        $results = $qb->getQuery()->getResult();
        $names = array();

        foreach ($results as $result) {
            $name = preg_replace('/ &.+; /', ' ', $result['LocalName']);
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

    public function findAutocomplete()
    {
        $companyNames = array();
        $infoPageNames = array();

        # находим компании
        $companies = $this->_em->createQuery('
			SELECT DISTINCT c.LocalName
			FROM VidalDrugBundle:Company c
			WHERE c.countProducts > 0 AND c.inactive = 0
		')->getResult();

        foreach ($companies as $company) {
            $name = preg_replace('/ &.+; /', ' ', $company['LocalName']);
            $name = preg_replace('/&.+;/', ' ', $name);
            $name = mb_strtolower($name, 'utf-8');
            $companyNames[] = $name;
        }

        # находим представительства
        $infoPages = $this->_em->createQuery('
			SELECT DISTINCT i.RusName
			FROM VidalDrugBundle:InfoPage i
			WHERE i.countProducts > 0
		')->getResult();

        foreach ($infoPages as $infoPage) {
            $name = preg_replace('/ &.+; /', ' ', $infoPage['RusName']);
            $name = preg_replace('/&.+;/', ' ', $name);
            $name = mb_strtolower($name, 'utf-8');
            $infoPageNames[] = $name;
        }

        $names = array_merge($companyNames, $infoPageNames);
        $names = array_unique($names);
        usort($names, 'strcasecmp');

        return $names;
    }

    public function findByQuery($q)
    {
        $words = explode(' ', $q);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('c.CompanyID, c.LocalName, c.Property, country.RusName Country')
            ->from('VidalDrugBundle:Company', 'c')
            ->leftJoin('VidalDrugBundle:Country', 'country', 'WITH', 'country.CountryCode = c.CountryCode')
            ->leftJoin('c.CompanyGroupID', 'g')
            ->orderBy('c.LocalName', 'ASC');

        # поиск по всем словам
        $qb->where("c.countProducts > 0")->andWhere("c.inactive = 0")->andWhere($this->where($words, 'AND'));
        $results = $qb->getQuery()->getResult();

        if (!empty($results)) {
            return $results;
        }

        # поиск по любому слову
        foreach ($words as $word) {
            if (mb_strlen($word, 'utf-8') < 3) {
                return array();
            }
        }

        $words = $this->getWords($q);
        $qb->where("c.countProducts > 0")->andWhere("c.inactive = 0")->andWhere($this->where($words, 'OR'));

        return $qb->getQuery()->getResult();
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
            $where .= "(c.LocalName LIKE '$word%' OR c.LocalName LIKE '% $word%' OR g.RusName LIKE '$word%' OR g.RusName LIKE '% $word%')";
            $i++;
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
            'A', 'B', 'V', 'G', 'D', 'E', 'YO', 'ZH', 'Z', 'I', 'J',
            'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
            'H', 'C', 'CH', 'SH', 'SHH', '', 'Y', '', 'E', 'YU', 'IA',
            'a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'j',
            'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
            'h', 'c', 'ch', 'sh', 'shh', '', 'y', '', 'e', 'yu', 'ya',
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
}