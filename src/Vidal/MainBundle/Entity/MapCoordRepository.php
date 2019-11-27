<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MapCoordRepository extends EntityRepository
{
	public function byRegion($regionId)
	{
		return $this->_em->createQuery('
			SELECT c.latitude x, c.longitude y, c.offerId id
			FROM VidalMainBundle:MapCoord c
			WHERE c.region = :regionId
		')->setParameter('regionId', $regionId)
			->getResult();
	}

	public function getObjects($regionId = null)
	{
		$data = array('type' => 'FeatureCollection', 'features' => array());

		$qb = $this->_em->createQueryBuilder();

		$qb->select('c.latitude x, c.longitude y, c.offerId id')
			->from('VidalMainBundle:MapCoord', 'c');

		if ($regionId) {
			if ($regionId == 87) {
				$regionId = 84; # Москва => Московская область
			}
			$qb->where('c.region = :regionId')->setParameter('regionId', $regionId);
		}

		$coords = $qb->getQuery()->getResult();

		for ($i = 0; $i < count($coords); $i++) {
			$coord = array(
				'type'       => 'Feature',
				'id'         => $coords[$i]['id'],
				'geometry'   => array(
					'type'        => 'Point',
					'coordinates' => array($coords[$i]['x'], $coords[$i]['y']),
				),
				'properties' => array(
					'balloonContent' => '',
				),
			);

			$data['features'][] = $coord;
		}

		return $data;
	}
}