<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AstrazenecaFaqRepository extends EntityRepository
{
	public function findAll()
	{
		return $this->_em->createQuery('
		 	SELECT f
		 	FROM VidalMainBundle:AstrazenecaFaq f
		 	ORDER BY f.enabled DESC, f.created DESC
		');
	}

	public function findActive()
	{
		return $this->_em->createQuery('
			SELECT f
			FROM VidalMainBundle:AstrazenecaFaq f
			WHERE f.enabled = TRUE
				AND f.answer IS NOT NULL
				AND LENGTH(f.answer) > 0
			ORDER BY f.created DESC
		')->getResult();
	}
}