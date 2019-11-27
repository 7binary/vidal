<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ArtCategoryRepository extends EntityRepository
{
	public function typeUrl($type, $url)
	{
		if (!$type) {
			return null;
		}

		return $this->_em->createQuery('
			SELECT c
			FROM VidalDrugBundle:ArtCategory c
			WHERE c.enabled = 1
				AND c.type = :type
				AND c.url = :url
		')->setParameter('type', $type->getId())
			->setParameter('url', $url)
			->getOneOrNullResult();
	}

	public function findByType($type)
	{
		if (!$type) {
			return null;
		}

		return $this->_em->createQuery('
			SELECT c
			FROM VidalDrugBundle:ArtCategory c
			WHERE c.enabled = 1 AND c.type = :type
			ORDER BY c.priority DESC, c.title ASC
		')->setParameter('type', $type->getId())
			->getResult();
	}
}