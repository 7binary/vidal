<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{
	public function getChoices()
	{
		$raw = $this->_em->createQuery('
			SELECT c.id, c.title, SIZE(c.doctors) as total
			FROM VidalMainBundle:Country c
			WHERE c.doctors IS NOT EMPTY
			ORDER BY c.title ASC
		')->getResult();

		$cities = array();

		foreach ($raw as $r) {
			$key          = $r['id'];
			$cities[$key] = '[' . $r['total'] . '] ' . $r['title'];
		}

		return $cities;
	}
}