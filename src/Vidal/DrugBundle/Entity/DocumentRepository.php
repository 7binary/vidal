<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentRepository extends EntityRepository
{
	public function findOneByDocumentID($id)
	{
		return $this->createQueryBuilder('d')
			->select('d')
			->where('d.DocumentID = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();
	}

	public function findById($id)
	{
		return $this->createQueryBuilder('d')
			->select('d')
			->where('d.DocumentID = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();
	}

    public function findOneById($id)
    {
        return $this->findById($id);
    }

	public function findOneByName($name)
	{
		return $this->_em->createQuery('
			SELECT d
			FROM VidalDrugBundle:Document d
			WHERE d.Name = :name
		')->setParameter('name', $name)
			->setMaxResults(1)
			->getOneOrNullResult();
	}

	public function findGenerics($documentIds)
	{
		$raw = $this->_em->createQuery('
		 	SELECT d.DocumentID, d.ShowGenericsOnlyInGNList generic
		 	FROM VidalDrugBundle:Document d
		 	WHERE d.DocumentID IN (:documentIds)
		')->setParameter('documentIds', $documentIds)
			->getResult();

		$generics = array();

		foreach ($raw as $r) {
			$key            = $r['DocumentID'];
			$generics[$key] = $r['generic'];
		}

		return $generics;
	}

	public function findByName($name)
	{
		# обрезаем расширение после точки и разбиваем по тире
		$pos = strpos($name, '.');
		if ($pos) {
			$name = substr($name, 0, $pos);
		}
		$name  = strtoupper($name);
		$names = explode('-', $name);

		# ищем документ с ArticleID 2,5
		$qb = $this->createQueryBuilder('d')
			->select('d')
			->andWhere('d.ArticleID IN (2,5)')
			->orderBy('d.ArticleID', 'ASC')
			->addOrderBy('d.YearEdition', 'DESC')
			->setMaxResults(1);

		$count = count($names);

		if ($count == 1) {
			$qb->andWhere("d.Name = '{$name}'");
		}
		else {
			for ($i = 0; $i < $count; $i++) {
				$word = $names[$i];
				if ($i == 0) {
					$qb->andWhere("d.Name LIKE '{$word}%'");
				}
				elseif ($i == $count - 1) {
					$qb->andWhere("d.Name LIKE '%{$word}'");
				}
				else {
					$qb->andWhere("d.Name LIKE '%{$word}%'");
				}
			}
		}
		$document = $qb->getQuery()->getOneOrNullResult();

		# ищем документ с ArticleID 4,3,1
		if (!$document) {
			$qb = $this->createQueryBuilder('d')
				->select('d')
				->andWhere('d.ArticleID IN (4,3,1)')
				->orderBy('d.ArticleID', 'DESC')
				->addOrderBy('d.YearEdition', 'DESC')
				->setMaxResults(1);

			if ($count == 1) {
				$qb->andWhere("d.Name = '{$name}'");
			}
			else {
				for ($i = 0; $i < $count; $i++) {
					$word = $names[$i];
					if ($i == 0) {
						$qb->andWhere("d.Name LIKE '{$word}%'");
					}
					elseif ($i == $count - 1) {
						$qb->andWhere("d.Name LIKE '%{$word}'");
					}
					else {
						$qb->andWhere("d.Name LIKE '%{$word}%'");
					}
				}
			}

			$document = $qb->getQuery()->getOneOrNullResult();
		}

		return $document;
	}

	public function findByMoleculeID($MoleculeID)
	{
	    $pdo = $this->_em->getConnection();

	    $sql = "
	        select doc.DocumentID, doc.RusName, doc.ArticleID, doc.YearEdition
            from document doc
            where doc.ArticleID in (1,8)
                and (select COUNT(*)
                from molecule_document md
                  where md.DocumentID = doc.DocumentID) = 1
                    and exists(
                      select 1 from molecule_document md
                      where md.DocumentID = doc.DocumentID
                      and md.MoleculeID = $MoleculeID
                    )
            order by doc.ArticleID
	    ";

	    $query = $pdo->prepare($sql);
	    $query->execute();
	    $results = $query->fetchAll();

	    if (empty($results)) {
	        return null;
        }

		return $this->_em->createQuery('
			SELECT d
			FROM VidalDrugBundle:Document d
			WHERE d.DocumentID = :DocumentID
		')->setParameter('DocumentID', $results[0]['DocumentID'])
			->setMaxResults(1)
			->getOneOrNullResult();
	}

	public function findByNozologyCode($code)
	{
		return $this->_em->createQuery("
			SELECT DISTINCT d.DocumentID, d.ArticleID, d.CountryEditionCode
			FROM VidalDrugBundle:Document d
			JOIN d.nozologies n WITH n.Code = :code
			WHERE d.ArticleID IN (2,4,5,1)
		")->setParameter('code', $code)
			->getResult();
	}

	public function findClPhGroupsByQuery($q)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('DISTINCT d.ClPhGrName name, d.ClPhGrDescription description')
			->from('VidalDrugBundle:Document', 'd')
			->orderBy('d.ClPhGrName', 'ASC');

		# поиск всем по словам
		$where = '';
		$words = explode(' ', $q);

		for ($i = 0; $i < count($words); $i++) {
			$word = $words[$i];
			if ($i > 0) {
				$where .= ' OR ';
			}
			$where .= "(d.ClPhGrName LIKE '$word%' OR d.ClPhGrName LIKE '% $word%')";
		}

		$qb->andWhere($where);
		$groups = $qb->getQuery()->getResult();

		if (empty($groups)) {
			foreach ($words as $word) {
				if (mb_strlen($word, 'utf-8') < 3) {
					return array();
				}
			}

			$where = '';

			for ($i = 0; $i < count($words); $i++) {
				$word = $words[$i];
				if ($i > 0) {
					$where .= ' AND ';
				}
				$where .= "(d.ClPhGrName LIKE '$word%' OR d.ClPhGrName LIKE '% $word%')";
			}

			$qb->where($where);
			$groups = $qb->getQuery()->getResult();
		}

		for ($i = 0, $c = count($groups); $i < $c; $i++) {
			$groups[$i]['description'] = preg_replace('/' . $q . '/iu', '<span class="query">$0</span>', $groups[$i]['description']);
		}

		return $groups;
	}

	public function findIdsByInfoPageID($InfoPageID)
	{
		$documentsRaw = $this->_em->createQuery('
			SELECT DISTINCT d.DocumentID
			FROM VidalDrugBundle:Document d
			JOIN d.infoPages i
			WHERE i.InfoPageID = :InfoPageID
				AND d.ArticleID NOT IN (1)
			ORDER BY d.DocumentID
		')->setParameter('InfoPageID', $InfoPageID)
			->getResult();

		$documents = array();

		foreach ($documentsRaw as $document) {
			$documents[] = $document['DocumentID'];
		}

		return $documents;
	}

	public function findIdsByNozologyContraCodes($nozologyCodes, $contraCodes)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('DISTINCT d.DocumentID')
			->from('VidalDrugBundle:Document', 'd');

		if (!empty($nozologyCodes)) {
			$qb->join('d.nozologies', 'n', 'WITH', 'n.NozologyCode IN (:nozologyCodes)')
				->setParameter('nozologyCodes', $nozologyCodes);
		}

		$documents   = $qb->getQuery()->getResult();
		$documentIds = array();

		for ($i = 0, $c = count($documents); $i < $c; $i++) {
			$documentIds[] = $documents[$i]['DocumentID'];
		}

		return $documentIds;
	}

	public function findIndicationsByProductIds($productIds)
	{
		$raw = $this->_em->createQuery('
			SELECT p.ProductID, d.Indication
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p.ProductID IN (:productIds)
		')->setParameter('productIds', $productIds)
			->getResult();

		$indications = array();

		for ($i = 0; $i < count($raw); $i++) {
			$key               = $raw[$i]['ProductID'];
			$indications[$key] = $raw[$i]['Indication'];
		}

		return $indications;
	}

	public function findOneByText($text)
	{
		$pos = strpos($text, ' ');
		$id  = intval(substr($text, 0, $pos));

		return $this->_em->createQuery('
			SELECT d
			FROM VidalDrugBundle:Document d
			WHERE d.DocumentID = :id
		')->setParameter('id', $id)
			->getOneOrNullResult();
	}

	public function findMaxId()
	{
		return $this->_em->createQuery('
			SELECT MAX(d.DocumentID)
			FROM VidalDrugBundle:Document d
		')->getSingleScalarResult();
	}
}