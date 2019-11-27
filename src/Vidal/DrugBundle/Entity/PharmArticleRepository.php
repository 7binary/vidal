<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PharmArticleRepository extends EntityRepository
{
	public function findByCompanyId($companyId)
	{
		return $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalDrugBundle:PharmArticle a
		 	WHERE a.enabled = TRUE
		 		AND a.company = :companyId
		 		AND a.created < :now
		 	ORDER BY a.created DESC
		')->setParameter('now', new \DateTime())
			->setParameter('companyId', $companyId)
			->getResult();
	}

	public function getQuery()
	{
		return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:PharmArticle a
			WHERE a.enabled = TRUE
				AND a.created < :now
			ORDER BY a.priority DESC, a.created DESC
		')->setParameter('now', new \DateTime());
	}

	public function getQueryOfCompany($id)
	{
		return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:PharmArticle a
			JOIN a.companies c WITH c.id = :id
			WHERE a.enabled = TRUE
				AND a.created < :now
			ORDER BY a.priority DESC, a.created DESC
		')->setParameter('now', new \DateTime())
			->setParameter('id', $id);
	}

	public function getQueryByTag($tagId)
	{
		return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:PharmArticle a
			JOIN a.tags t
			WHERE a.enabled = TRUE
				AND a.created < :now
				AND t = :tagId
			ORDER BY a.created DESC, a.priority DESC
		')->setParameter('now', new \DateTime())
			->setParameter('tagId', $tagId);
	}
}