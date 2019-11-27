<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RegionRepository extends EntityRepository
{
	public function getChoices()
	{
		$raw = $this->_em->createQuery('
			SELECT c.id, c.title, SIZE(c.doctors) as total, co.title as country
			FROM VidalMainBundle:Region c
			JOIN c.country co
			WHERE c.doctors IS NOT EMPTY
			ORDER BY c.title ASC
		')->getResult();

		$cities = array();

		foreach ($raw as $r) {
			$key          = $r['id'];
			$cities[$key] = '[' . $r['total'] . '] ' . $r['title'] . ' -> ' . $r['country'];
		}

		return $cities;
	}
}