<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TagHistoryRepository extends EntityRepository
{
	public function findOneByTagText($tagId, $text)
	{
		return $this->_em->createQuery('
			SELECT h
			FROM VidalDrugBundle:TagHistory h
			WHERE h.tag = :tagId
				AND h.text = :text
		')->setParameter('tagId', $tagId)
			->setParameter('text', $text)
			->setMaxResults(1)
			->getOneOrNullResult();
	}
}