<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ShareRepository extends EntityRepository
{
	public function countBy($class, $target)
	{
		return $this->_em->createQuery('
			SELECT COUNT(s.id)
			FROM VidalMainBundle:Share s
			WHERE s.class = :class
				AND s.target = :target
		')
			->setParameter('class', $class)
			->setParameter('target', $target)
			->getSingleScalarResult();
	}
}