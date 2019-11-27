<?php

namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ContraindicationRepository extends EntityRepository
{
	public function findAll()
	{
		return $this->_em->createQuery('
			SELECT c.RusName, c.ContraIndicCode
			FROM VidalVeterinarBundle:Contraindication c
			ORDER BY c.RusName ASC
		')->getResult();
	}

	public function findByCodes($contraCodes)
	{
		return $this->_em->createQuery('
			SELECT DISTINCT c.ContraIndicCode, c.RusName
			FROM VidalVeterinarBundle:Contraindication c
			WHERE c.ContraIndicCode IN (:contraCodes)
		')->setParameter('contraCodes', $contraCodes)
			->getResult();
	}
}