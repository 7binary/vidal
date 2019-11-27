<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AstrazenecaNewRepository extends EntityRepository
{
	public function findActive()
	{
		return $this->_em->createQuery('
			SELECT n
			FROM VidalMainBundle:AstrazenecaNew n
			WHERE n.enabled = TRUE
			ORDER BY n.created DESC
		')->getResult();
	}
}
