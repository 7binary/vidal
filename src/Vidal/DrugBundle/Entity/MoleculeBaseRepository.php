<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MoleculeBaseRepository extends EntityRepository
{
	public function findAll()
	{
		return $this->_em->createQuery("
		 	SELECT m
		 	FROM VidalDrugBundle:MoleculeBase m
		 	WHERE m.GNParent != 'Unknown'
		 	ORDER BY m.GNParent ASC
        ")->getResult();
	}
}