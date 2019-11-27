<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AstrazenecaBlogRepository extends EntityRepository
{
	public function findActive()
	{
		return $this->_em->createQuery('
		 	SELECT b
		 	FROM VidalMainBundle:AstrazenecaBlog b
		 	WHERE b.enabled = TRUE
		 	ORDER BY b.priority DESC, b.id DESC
		')->getResult();
	}
}