<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AboutRepository extends EntityRepository
{
	public function findAbout()
	{
		return $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalMainBundle:About a
		 	WHERE a.enabled = TRUE
		 	ORDER BY a.priority DESC, a.title DESC
		')->getResult();
	}

	public function findSitemap()
	{
		return $this->_em->createQuery('
		 	SELECT a.title, a.url
		 	FROM VidalMainBundle:About a
		 	WHERE a.enabled = TRUE
		 	ORDER BY a.title
		')->getResult();
	}
}