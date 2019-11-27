<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class QuestionAnswerRepository extends EntityRepository
{
	public function findByEnabled()
	{
		return $this->_em->createQuery('
			SELECT qa
			FROM VidalMainBundle:QuestionAnswer qa
			WHERE qa.enabled = TRUE
			ORDER BY qa.created DESC
		');
	}

	public function findAll()
	{
		return $this->_em->createQueryBuilder()
			->select('qa')
			->from('VidalMainBundle:QuestionAnswer', 'qa')
			->where('qa.enabled = 1')
			->orderBy('qa.created', 'DESC')
			->getQuery()
			->getResult();
	}
}