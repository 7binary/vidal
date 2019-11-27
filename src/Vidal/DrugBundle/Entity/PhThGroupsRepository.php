<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PhThGroupsRepository extends EntityRepository
{
	public function findById($id)
	{
		return $this->_em->createQuery('
			SELECT g.Name, g.id
			FROM VidalDrugBundle:PhThGroups g
			WHERE g = :id
		')->setParameter('id', $id)
			->getOneOrNullResult();
	}

	public function findByProductID($ProductID)
	{
		return $this->_em->createQuery('
			SELECT g.Name, g.id
			FROM VidalDrugBundle:PhThGroups g
			JOIN g.products p WITH p = :ProductID
			ORDER BY g.Name
		')->setParameter('ProductID', $ProductID)
			->getResult();
	}

	public function getQuery()
	{
		return $this->_em->createQuery('
		 	SELECT g
		 	FROM VidalDrugBundle:PhThGroups g
		 	ORDER BY g.Name
		');
	}

	public function getQueryByLetter($l)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb
			->select('g')
			->from('VidalDrugBundle:PhThGroups', 'g')
			->orderBy('g.Name', 'ASC')
			->where('g.Name LIKE :l')
			->setParameter('l', $l . '%');

		return $qb->getQuery();
	}

	public function findByQueryString($q)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb
			->select('g')
			->from('VidalDrugBundle:PhThGroups', 'g')
			->orderBy('g.Name', 'ASC');

		# поиск по всем словам
		$where = '';
		$words = explode(' ', $q);

		for ($i = 0; $i < count($words); $i++) {
			$word = $words[$i];
			if ($i > 0) {
				$where .= ' AND ';
			}
			$where .= "(g.Name LIKE '$word%' OR g.Name LIKE '% $word%')";
		}

		$qb->where($where);
		$results = $qb->getQuery()->getResult();

		# поиск по одному слову
		if (empty($results)) {
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
				$where .= "(g.Name LIKE '$word%' OR g.Name LIKE '% $word%')";
			}

			$qb->where($where);

			return $qb->getQuery()->getResult();
		}

		return $results;
	}

	public function getNames()
	{
		$qb = $this->_em->createQueryBuilder();

		$qb
			->select('g.Name')
			->from('VidalDrugBundle:PhThGroups', 'g')
			->orderBy('g.Name', 'ASC');

		$results = $qb->getQuery()->getResult();
		$names   = array();

		foreach ($results as $result) {
			$name = preg_replace('/ &.+; /', ' ', $result['Name']);
			$name = preg_replace('/&.+;/', ' ', $name);

			$names[] = mb_strtolower($name, 'utf-8');
		}

		$uniques = array();

		foreach ($names as $name) {
			if (!isset($uniques[$name])) {
				$uniques[$name] = '';
			}
		}

		return array_keys($uniques);
	}

	public function getOptions()
	{
		$raw = $this->_em->createQuery('
			SELECT g.id, g.Name
			FROM VidalDrugBundle:PhThGroups g
		 	ORDER BY g.Name ASC
		 ')->getResult();

		$items = array();

		foreach ($raw as $r) {
			$items[] = array(
				'id'    => $r['id'],
				'title' => $r['Name'],
			);
		}

		return $items;
	}

    public function adminAutocomplete($term)
    {
        $codes = $this->_em->createQuery('
			SELECT g.id, g.Name
			FROM VidalDrugBundle:PhThGroups g
			WHERE g.id LIKE :id
				OR g.Name LIKE :term
			ORDER BY g.Name ASC
		')->setParameter('id', $term . '%')
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($codes as $code) {
            $data[] = array(
                'id' => $code['id'],
                'text' => $code['id'] . ' - ' . $code['Name']
            );
        }

        return $data;
    }
}