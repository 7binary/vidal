<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AboutServiceRepository extends EntityRepository
{
	public function findServices()
	{
		return $this->_em->createQuery('
		 	SELECT s
		 	FROM VidalMainBundle:AboutService s
		 	WHERE s.enabled = TRUE
		 	ORDER BY s.priority DESC, s.title DESC
		')->getResult();
	}

	public function findSitemap()
	{
		return $this->_em->createQuery('
		 	SELECT a.title, a.url
		 	FROM VidalMainBundle:AboutService a
		 	WHERE a.enabled = TRUE
		 	ORDER BY a.title
		')->getResult();
	}
}