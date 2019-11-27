<?php

namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ClinicoPhPointersRepository extends EntityRepository
{
	public function findForTree()
	{
		$results = $this->_em->createQuery('
		 	SELECT c.Code, c.Name, c.Level, c.ClPhPointerID, c.url, c.countProducts
		 	FROM VidalVeterinarBundle:ClinicoPhPointers c
		 	ORDER BY c.Name
		')->getResult();

		$codes = array();

		foreach ($results as $code) {
			$key         = $code['Code'];
			$codes[$key] = $code;
		}

		return $codes;
	}

	public function isFinal($kfu)
	{
		$code      = $kfu->getCode();
		$nextCodes = array($code . '.01', $code . '.02', $code . '.03');

		$count = $this->_em->createQuery('
			SELECT COUNT(c.ClPhPointerID)
			FROM VidalVeterinarBundle:ClinicoPhPointers c
			WHERE c.Code IN (:nextCodes)
		')->setParameter('nextCodes', $nextCodes)
			->getSingleScalarResult();

		return $count ? false : true;
	}

	public function updateTotal($id, $total)
	{
		return $this->_em->createQuery('
			UPDATE VidalVeterinarBundle:ClinicoPhPointers c
			SET c.total = :total
			WHERE c.ClPhPointerID = :id
		')->setParameters(array(
				'id'    => $id,
				'total' => $total,
			))
			->execute();
	}

    public function countProducts()
    {
        return $this->_em->createQuery('
			SELECT COUNT(p.ProductID) as countProducts, pointer.ClPhPointerID
			FROM VidalVeterinarBundle:Product p
			LEFT JOIN p.document d
			JOIN d.clphPointers pointer
			WHERE p.CountryEditionCode = \'RUS\'
				AND (p.MarketStatusID = 1 OR p.MarketStatusID = 2)
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
			GROUP BY pointer.ClPhPointerID
		')->getResult();
    }

    public function jsonForTree()
    {
        $results = $this->_em->createQuery('
		 	SELECT c.Name as text, c.Code as id, c.countProducts, c.url
		 	FROM VidalVeterinarBundle:ClinicoPhPointers c
		 	ORDER BY c.Code ASC
		')->getResult();

        $codes = array();

        foreach ($results as $code) {
            $key         = $code['id'];
            $codes[$key] = $code;
        }

        return $codes;
    }
}