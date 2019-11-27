<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DocumentRepository extends EntityRepository
{
	public function findById($id)
	{
		return $this->createQueryBuilder('d')
			->select('d')
			->where('d.DocumentID = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();
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
			->andWhere("d.CountryEditionCode = 'RUS'")
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
				->andWhere('d.ArticleID IN (4,3,1,6,7,8,9,10,11,12)')
				->andWhere("d.CountryEditionCode = 'RUS'")
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

	public function findIdsByInfoPageID($InfoPageID)
	{
		$documentsRaw = $this->_em->createQuery('
			SELECT DISTINCT d.DocumentID
			FROM VidalVeterinarBundle:Document d
			JOIN d.infoPages i
			WHERE i.InfoPageID = :InfoPageID
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
			->from('VidalVeterinarBundle:Document', 'd');

		if (!empty($nozologyCodes)) {
			$qb->join('d.nozologies', 'n', 'WITH', 'n.NozologyCode IN (:nozologyCodes)')
				->setParameter('nozologyCodes', $nozologyCodes);
		}

		if (!empty($contraCodes)) {
			$qb->join('d.contraindications', 'c', 'WITH', 'c.ContraIndicCode NOT IN (:contraCodes)')
				->setParameter('contraCodes', $contraCodes);
		}

		$documents = $qb->getQuery()->getResult();
		$documentIds = array();

		for ($i=0, $c=count($documents); $i<$c; $i++) {
			$documentIds[] = $documents[$i]['DocumentID'];
		}

		return $documentIds;
	}

	public function findIndicationsByProductIds($productIds)
	{
		$raw = $this->_em->createQuery('
			SELECT p.ProductID, d.Indication
			FROM VidalVeterinarBundle:Product p
			LEFT JOIN p.document d
			WHERE p.ProductID IN (:productIds)
		')->setParameter('productIds', $productIds)
			->getResult();

		$indications = array();

		for ($i=0; $i<count($raw); $i++) {
			$key = $raw[$i]['ProductID'];
			$indications[$key] = $raw[$i]['Indication'];
		}

		return $indications;
	}

	public function findByMoleculeID($MoleculeID)
	{
		return $this->_em->createQuery('
			SELECT d
			FROM VidalVeterinarBundle:Document d
			LEFT JOIN d.molecules m
			WHERE m.MoleculeID = :MoleculeID
			  AND d.ArticleID = 1
			ORDER BY d.YearEdition DESC
		')->setParameter('MoleculeID', $MoleculeID)
			->setMaxResults(1)
			->getOneOrNullResult();
	}

	public function findByProductID($ProductID)
	{
		return $this->_em->createQuery('
			SELECT d
			FROM VidalVeterinarBundle:Document d
			JOIN d.products p WITH p = :ProductID
		')->setParameter('ProductID', $ProductID)
			->setMaxResults(1)
			->getOneOrNullResult();
	}
}