<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AnaliticsRepository extends EntityRepository
{
	public function get()
	{
		$analitics = $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalDrugBundle:Analitics a
		')->setMaxResults(1)
			->getOneOrNullResult();

		if (!$analitics) {
		    $analitics = new Analitics();
			$this->_em->persist($analitics);
			$this->_em->flush($analitics);
			$this->_em->refresh($analitics);
		}

		return $analitics;
	}
}