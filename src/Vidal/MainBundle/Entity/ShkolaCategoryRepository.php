<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ShkolaCategoryRepository extends EntityRepository
{
	public function findAll()
	{
		return $this->_em->createQuery('
			SELECT c
			FROM VidalMainBundle:ShkolaCategory c
			WHERE c.enabled = TRUE
			ORDER BY c.priority ASC
		')->getResult();
	}
}