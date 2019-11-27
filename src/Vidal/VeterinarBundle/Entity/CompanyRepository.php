<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository
{
    public function findOneByCompanyID($CompanyID)
    {
        return $this->_em->createQuery('
			SELECT c
			FROM VidalVeterinarBundle:Company c
			WHERE c = :CompanyID
		')->setParameter('CompanyID', $CompanyID)
            ->getOneOrNullResult();
    }

	public function findByLetter($l)
	{
		return $this->_em->createQuery('
		 	SELECT c.CompanyID, c.LocalName, c.Property, country.RusName Country, c.Name, c.countProducts
		 	FROM VidalVeterinarBundle:Company c
		 	LEFT JOIN VidalVeterinarBundle:Country country WITH country.CountryCode = c.CountryCode
		 	WHERE c.LocalName LIKE :letter
		')->setParameter('letter', $l . '%')
			->getResult();
	}

    public function getNames()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb
            ->select('c.LocalName')
            ->from('VidalVeterinarBundle:Company', 'c')
            ->orderBy('c.LocalName', 'ASC')
            ->where("c.CountryEditionCode = 'RUS'");

        $results = $qb->getQuery()->getResult();
        $names   = array();

        foreach ($results as $result) {
            $name = preg_replace('/ &.+; /', ' ', $result['LocalName']);
            $name = preg_replace('/&.+;/', ' ', $name);
            $name = mb_strtolower($name, 'UTF-8');

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

    public function findByCompanyID($CompanyID)
	{
		return $this->_em->createQuery('
			SELECT c.CompanyID, c.LocalName CompanyName, c.Property, country.RusName Country, c.countProducts
			FROM VidalVeterinarBundle:Company c
			LEFT JOIN VidalVeterinarBundle:Country country WITH c.CountryCode = country
			WHERE c = :CompanyID
		')->setParameter('CompanyID', $CompanyID)
			->getOneOrNullResult();
	}

	public function findOneByName($name)
	{
		return $this->_em->createQuery('
			SELECT c.CompanyID, c.LocalName CompanyName, c.Property, country.RusName Country, c.countProducts
			FROM VidalVeterinarBundle:Company c
			LEFT JOIN VidalVeterinarBundle:Country country WITH c.CountryCode = country
			WHERE c.Name = :name
		')->setParameter('name', $name)->setMaxResults(1)
			->getOneOrNullResult();
	}

	public function findAllOrdered()
	{
		return $this->_em->createQuery('
			SELECT c.LocalName, c.Name, country.RusName Country, c.countProducts
			FROM VidalVeterinarBundle:Company c
			LEFT JOIN VidalVeterinarBundle:Country country WITH c.CountryCode = country
			WHERE c.countProducts > 0
			ORDER BY c.LocalName ASC
		')->getResult();
	}

	public function findByQuery($q)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb
			->select('c.CompanyID, c.LocalName, c.Property, country.RusName Country, c.Name, c.countProducts')
			->from('VidalVeterinarBundle:Company', 'c')
			->leftJoin('VidalVeterinarBundle:Country', 'country', 'WITH', 'country.CountryCode = c.CountryCode')
			->orderBy('c.LocalName', 'ASC');

		# поиск по всем словам
		$where = '';
		$words = explode(' ', $q);

		for ($i = 0; $i < count($words); $i++) {
			$word = $words[$i];
			if ($i > 0) {
				$where .= ' AND ';
			}
			$where .= "(c.LocalName LIKE '%$word%')";
		}

		$qb->where($where);
        $qb->andWhere('c.countProducts > 0');
		$results = $qb->getQuery()->getResult();

		# поиск по одному слову
		if (empty($results)) {
			$where = '';
			for ($i = 0; $i < count($words); $i++) {
				$word = $words[$i];
				if ($i > 0) {
					$where .= ' OR ';
				}
				$where .= "(c.LocalName LIKE '$word%' OR c.LocalName LIKE '% $word%')";
			}
			$qb->where($where);
            $qb->andWhere('c.countProducts > 0');

			return $qb->getQuery()->getResult();
		}

		return $results;
	}

	public function findOwnersByProducts($productIds)
	{
		return $this->_em->createQuery('
			SELECT DISTINCT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property, c.Name,
				country.RusName Country, c.countProducts
			FROM VidalVeterinarBundle:Company c
			LEFT JOIN VidalVeterinarBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalVeterinarBundle:Country country WITH c.CountryCode = country
			WHERE pc.ProductID IN (:productIds) AND
				pc.ItsMainCompany = 1
		')->setParameter('productIds', $productIds)
			->getResult();
	}

	public function findDistributorsByProducts($productIds)
	{
		return $this->_em->createQuery('
			SELECT DISTINCT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property, c.Name,
				country.RusName Country, c.countProducts
			FROM VidalVeterinarBundle:Company c
			LEFT JOIN VidalVeterinarBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalVeterinarBundle:Country country WITH c.CountryCode = country
			WHERE pc.ProductID IN (:productIds) AND
				pc.ItsMainCompany = 0
			ORDER BY pc.CompanyRusNote ASC
		')->setParameter('productIds', $productIds)
			->getResult();
	}

	public function findByProducts($productIds)
	{
		$companies = $this->_em->createQuery('
			SELECT c.CompanyID, pc.CompanyRusNote, pc.CompanyEngNote, c.LocalName, c.Property, c.Name,
				country.RusName Country, pc.ItsMainCompany, p.ProductID
			FROM VidalVeterinarBundle:Company c
			LEFT JOIN VidalVeterinarBundle:ProductCompany pc WITH pc.CompanyID = c
			LEFT JOIN VidalVeterinarBundle:Country country WITH c.CountryCode = country
			LEFT JOIN VidalVeterinarBundle:Product p WITH p = pc.ProductID
			WHERE pc.ProductID IN (:productIds)
			ORDER BY pc.ItsMainCompany DESC
		')->setParameter('productIds', $productIds)
			->getResult();

		$productCompanies = array();

		# надо получить компании и сгруппировать их по продукту
		foreach ($companies as $company) {
			$key = $company['ProductID'];
			isset($productCompanies[$key])
				? $productCompanies[$key][] = $company
				: $productCompanies[$key] = array($company);
		}

		return $productCompanies;
	}

    public function findForExcel()
    {
        return $this->_em->createQuery("
			SELECT c.CompanyID, c.LocalName, c.GDDBName, c.Property, c.countProducts, country.RusName Country
			FROM VidalVeterinarBundle:Company c
			LEFT JOIN VidalVeterinarBundle:Country country WITH c.CountryCode = country
			WHERE c.CountryEditionCode = 'RUS' AND c.countProducts > 0
			ORDER BY c.LocalName ASC
		")->getResult();
    }
}