<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MapRegionRepository extends EntityRepository
{
	public function byRegion($regionId)
	{
		return $this->_em->createQuery('
		 	SELECT r.latitude, r.longitude, r.zoom
		 	FROM VidalMainBundle:MapRegion r
		 	WHERE r.id = :regionId
		')->setParameter('regionId', $regionId)
			->getSingleResult();
	}
}