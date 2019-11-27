<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MoleculeBaseRepository extends EntityRepository
{
	public function findAll()
	{
		return $this->_em->createQuery("
		 	SELECT m
		 	FROM VidalVeterinarBundle:MoleculeBase m
		 	WHERE m.GNParent != 'Unknown'
		 	ORDER BY m.GNParent ASC
        ")->getResult();
	}
}