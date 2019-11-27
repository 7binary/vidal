<?php

namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PharmPortfolioRepository extends EntityRepository
{
	public function findActive()
	{
		return $this->_em->createQuery('
			SELECT p
			FROM VidalVeterinarBundle:PharmPortfolio p
			WHERE p.enabled = 1
			ORDER BY p.title ASC
		')->getResult();
	}
}