<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ArtTypeRepository extends EntityRepository
{
	public function rubriqueUrl($rubrique, $url)
	{
		return $this->_em->createQuery('
			SELECT t
			FROM VidalDrugBundle:ArtType t
			WHERE t.enabled = 1
				AND t.rubrique = :rubrique
				AND t.url = :url
		')->setParameter('rubrique', $rubrique->getId())
			->setParameter('url', $url)
			->getOneOrNullResult();
	}

    public function findByIds($ids)
    {
        return $this->_em->createQuery('
			SELECT t
			FROM VidalDrugBundle:ArtType t
			WHERE t.id IN (:ids)
		')->setParameter('ids', $ids)
            ->getResult();
    }

	public function findByRubrique($rubrique)
	{
		return $this->_em->createQuery('
			SELECT t
			FROM VidalDrugBundle:ArtType t
			WHERE t.enabled = 1 AND t.rubrique = :rubrique
			ORDER BY t.priority DESC, t.title ASC
		')->setParameter('rubrique', $rubrique->getId())
			->getResult();
	}
}