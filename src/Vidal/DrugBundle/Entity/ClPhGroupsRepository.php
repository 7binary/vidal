<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ClPhGroupsRepository extends EntityRepository
{
	public function findOneById($id)
	{
		return $this->_em->createQuery('
			SELECT g
			FROM VidalDrugBundle:ClPhGroups g
			WHERE g = :id
		')->setParameter('id', $id)
			->getOneOrNullResult();
	}

	public function getNames()
	{
		$raw = $this->_em->createQuery('
			SELECT DISTINCT g.Name
			FROM VidalDrugBundle:ClPhGroups g
			ORDER BY g.Name ASC
		')->getResult();

		$names = array();

		foreach ($raw as $r) {
			$names[] = mb_strtolower($r['Name'], 'utf-8');
		}

		return $names;
	}

	public function findWithProducts()
	{
		return $this->_em->createQuery('
			SELECT DISTINCT g, COUNT(p) AS HIDDEN total
			FROM VidalDrugBundle:ClPhGroups g
			JOIN g.products p WITH p.ProductTypeCode IN (\'DRUG\', \'GOME\')
			GROUP BY g
			HAVING total > 0
		')->getResult();
	}

	public function getQuery()
	{
		return $this->_em->createQuery('
			SELECT DISTINCT g, COUNT(p) AS HIDDEN total
			FROM VidalDrugBundle:ClPhGroups g
			JOIN g.products p WITH p.ProductTypeCode IN (\'DRUG\', \'GOME\')
			GROUP BY g
			HAVING total > 0
			ORDER BY g.Name ASC
		')->getResult();
	}

	public function findByLetter($l)
	{
		return $this->_em->createQuery('
			SELECT DISTINCT g, COUNT(p) AS HIDDEN total
			FROM VidalDrugBundle:ClPhGroups g
			JOIN g.products p WITH p.ProductTypeCode IN (\'DRUG\', \'GOME\')
			WHERE g.Name LIKE :letter
			GROUP BY g
			HAVING total > 0
			ORDER BY g.Name ASC
		')->setParameter('letter', $l . '%')
			->getResult();
	}

	public function findByQuery($q)
	{
		$words = explode(' ', $q);

		$qb = $this->_em->createQueryBuilder();
		$qb->select('DISTINCT g, COUNT(p) AS HIDDEN total')
			->from('VidalDrugBundle:ClPhGroups', 'g')
			->join('g.products', 'p', 'WITH', 'p.ProductTypeCode IN (\'DRUG\', \'GOME\')')
			->groupBy('g')
			->having('total > 0')
			->orderBy('g.Name', 'ASC');

		# поиск по всем словам вместе
		$qb->where($this->where($words, 'AND'));
		$results = $qb->getQuery()->getResult();

		if (!empty($results)) {
			return $results;
		}

		# поиск по любому из слов
		foreach ($words as $word) {
			if (mb_strlen($word, 'utf-8') < 3) {
				return array();
			}
		}

		$qb->where($this->where($words, 'OR'));
		$results = $qb->getQuery()->getResult();

		if (!empty($results)) {
			return $results;
		}

		return array();
	}

	private function where($words, $s)
	{
		$s = ($s == 'OR') ? ' OR ' : ' AND ';

		$where = '';
		for ($i = 0; $i < count($words); $i++) {
			$word = $words[$i];
			if ($i > 0) {
				$where .= $s;
			}
			$where .= "(g.Name LIKE '$word%' OR g.Name LIKE '% $word%')";
		}

		return $where;
	}

	public function getOptions()
	{
		$raw = $this->_em->createQuery('
			SELECT g.ClPhGroupsID, g.Name
			FROM VidalDrugBundle:ClPhGroups g
		 	ORDER BY g.Name ASC
		 ')->getResult();

		$items = array();

		foreach ($raw as $r) {
			$items[] = array(
				'id'    => $r['ClPhGroupsID'],
				'title' => $this->strip($r['Name'])
			);
		}

		return $items;
	}

	private function strip($string)
	{
		$pat = array('/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i', '/&amp;/');
		$rep = array('', '', '&');

		return preg_replace($pat, $rep, $string);
	}

    public function adminAutocomplete($term)
    {
        $codes = $this->_em->createQuery('
			SELECT g.ClPhGroupsID, g.Name
			FROM VidalDrugBundle:ClPhGroups g
			WHERE g.ClPhGroupsID LIKE :id
				OR g.Name LIKE :term
			ORDER BY g.Name ASC
		')->setParameter('id', $term . '%')
            ->setParameter('term', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($codes as $code) {
            $data[] = array(
                'id' => $code['ClPhGroupsID'],
                'text' => $code['ClPhGroupsID'] . ' - ' . $code['Name']
            );
        }

        return $data;
    }
}