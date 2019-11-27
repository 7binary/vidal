<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class InteractionRepository extends EntityRepository
{
	public function getQuery()
	{
		return $this->_em->createQuery('
		 	SELECT i
		 	FROM VidalDrugBundle:Interaction i
		 	ORDER BY i.RusName ASC
		');
	}

    public function getAll()
    {
        return $this->_em->createQuery('
		 	SELECT i.RusName, i.id, i.EngName, i.text
		 	FROM VidalDrugBundle:Interaction i
		 	ORDER BY i.RusName ASC
		')->getResult();
    }

    public function findMoreInteractions($currPage, $limit = 10)
    {
        return $this->_em->createQuery('
			SELECT i
			FROM VidalDrugBundle:Interaction i
			ORDER BY i.RusName ASC
		')->setFirstResult($currPage * $limit)
            ->setMaxResults($limit)
            ->getResult();
    }

	public function findOneByEngName($EngName)
	{
		return $this->_em->createQuery('
		 	SELECT i
		 	FROM VidalDrugBundle:Interaction i
		 	WHERE i.EngName = :EngName
		')->setParameter('EngName', $EngName)
			->getOneOrNullResult();
	}

	public function findByLetter($l)
	{
		return $this->_em->createQuery('
			SELECT i
			FROM VidalDrugBundle:Interaction i
			WHERE i.RusName LIKE :l
			ORDER BY i.RusName ASC
		')->setParameter('l', $l . '%')
			->getResult();
	}

	public function findByQuery($q)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('i')
			->from('VidalDrugBundle:Interaction', 'i')
			->orderBy('i.RusName', 'ASC');

		# поиск по словам
		$where = '';
		$words = explode(' ', $q);

		# находим все слова
		for ($i = 0; $i < count($words); $i++) {
			$word = $words[$i];
			if ($i > 0) {
				$where .= ' AND ';
			}
			$where .= "(i.RusName LIKE '$word%' OR i.RusName LIKE '% $word%')";
		}

		$qb->where($where);
		$results = $qb->getQuery()->getResult();

		# находим какое-либо из слов, если нет результата
		if (empty($results)) {
			$where = '';
			for ($i = 0; $i < count($words); $i++) {
				$word = $words[$i];
				if ($i > 0) {
					$where .= ' OR ';
				}
				$where .= "(i.RusName LIKE '$word%' OR i.RusName LIKE '% $word%')";
			}

			$qb->where($where);
			$results = $qb->getQuery()->getResult();
		}

		return $results;
	}
}