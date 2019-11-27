<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ClinicoPhPointersRepository extends EntityRepository
{
	public function findForTree()
	{
		return $this->_em->createQuery('
		 	SELECT c.Name as text, c.Code as id
		 	FROM VidalDrugBundle:ClinicoPhPointers c
		 	WHERE c.Level = 0
		 	ORDER BY c.Code ASC
		')->getResult();
	}

	public function jsonForTree()
	{
		$results = $this->_em->createQuery('
		 	SELECT c.Name as text, c.Code as id, c.countProducts
		 	FROM VidalDrugBundle:ClinicoPhPointers c
		 	ORDER BY c.Code ASC
		')->getResult();

		$codes = array();

		foreach ($results as $code) {
			$key         = $code['id'];
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
			FROM VidalDrugBundle:ClinicoPhPointers c
			WHERE c.Code IN (:nextCodes)
		')->setParameter('nextCodes', $nextCodes)
			->getSingleScalarResult();

		return $count ? false : true;
	}

	public function findOneById($id)
	{
		return $this->_em->createQuery('
			SELECT c
			FROM VidalDrugBundle:ClinicoPhPointers c
			WHERE c.ClPhPointerID = :id
		')->setParameter('id', $id)
			->getOneOrNullResult();
	}

	public function findOneByCode($Code)
	{
		return $this->_em->createQuery('
			SELECT c
			FROM VidalDrugBundle:ClinicoPhPointers c
			WHERE c.Code = :Code
		')->setParameter('Code', $Code)
			->getOneOrNullResult();
	}

	public function findByLetter($letter)
	{
		return $this->_em->createQuery('
			SELECT c.Code, c.Name, c.countProducts, c.Level
			FROM VidalDrugBundle:ClinicoPhPointers c
			WHERE c.Code LIKE :letter
			ORDER BY c.Code ASC
		')->setParameter('letter', $letter . '%')
			->getResult();
	}

	public function findBase($kfu)
	{
		$code = $kfu->getCode();
		$pos  = strpos($code, '.');

		if ($pos === false) {
			return null;
		}

		$codes = explode('.', $code);

		return $this->findOneByCode($codes[0]);
	}

	public function countProducts()
	{
		return $this->_em->createQuery('
			SELECT COUNT(p.ProductID) as countProducts, pointer.ClPhPointerID
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			JOIN d.clphPointers pointer
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode IN (\'DRUG\',\'GOME\')
				AND p.inactive = FALSE
			GROUP BY pointer.ClPhPointerID
		')->getResult();
	}

	public function findByQuery($q)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('n.Code, n.Name, n.Level, n.countProducts')
			->from('VidalDrugBundle:ClinicoPhPointers', 'n')
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
			$where .= "(n.Name LIKE '$word%' OR n.Name LIKE '% $word%')";
		}

		$qb->where($where);
		$pointers = $qb->getQuery()->getResult();

		# находим какое-либо из слов, если нет результата
		if (empty($pointers)) {
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

			$qb->where($where);
			$pointers = $qb->getQuery()->getResult();
		}

		return $pointers;
	}

	public function findAutocomplete()
	{
		$codes = $this->_em->createQuery('
			SELECT p.Code, p.Name
			FROM VidalDrugBundle:ClinicoPhPointers p
			ORDER BY p.Name ASC
		')->getResult();

		$names = array();

		for ($i = 0; $i < count($codes); $i++) {
			$patterns     = array('/<SUP>.*<\/SUP>/', '/<SUB>.*<\/SUB>/', '/&alpha;/', '/&amp;/');
			$replacements = array('', '', ' ', ' ');
			$name         = preg_replace($patterns, $replacements, $codes[$i]['Name']);
			$name         = mb_strtolower(str_replace('  ', ' ', $name), 'UTF-8');

			if (!empty($name) && !isset($names[$name])) {
				$names[$name] = '';
			}
		}

		return array_keys($names);
	}

    public function adminAutocomplete($term)
    {
        $codes = $this->_em->createQuery('
			SELECT p.ClPhPointerID, p.Name, p.Code
			FROM VidalDrugBundle:ClinicoPhPointers p
			WHERE p.Code LIKE :term1
				OR p.Name LIKE :term2
			ORDER BY p.Code ASC, p.Name ASC
		')->setParameter('term1', '%' . $term . '%')
            ->setParameter('term2', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($codes as $code) {
            $data[] = array(
                'id' => $code['ClPhPointerID'],
                'text' => $code['Code'] . ' - ' . $code['Name']
            );
        }

        return $data;
    }
}