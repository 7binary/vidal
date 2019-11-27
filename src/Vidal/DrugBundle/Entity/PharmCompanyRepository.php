<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PharmCompanyRepository extends EntityRepository
{
	public function findWithArticles()
	{
		return $this->_em->createQuery('
		 	SELECT c
		 	FROM VidalDrugBundle:PharmCompany c
		 	WHERE SIZE(c.articles) > 0
		 	ORDER BY c.title ASC
		')->getResult();
	}
}