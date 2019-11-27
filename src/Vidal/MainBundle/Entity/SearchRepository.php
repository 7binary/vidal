<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SearchRepository extends EntityRepository
{
	public function forExcel()
	{
		$raw = $this->_em->createQuery('
			SELECT s.query, s.referer, s.created, s.withoutResults, s.tooShort
			FROM VidalMainBundle:Search s
			ORDER BY s.created DESC
		')->getResult();

		return $raw;
	}
}