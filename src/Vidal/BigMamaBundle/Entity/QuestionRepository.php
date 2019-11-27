<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\EntityRepository;

class QuestionRepository extends EntityRepository
{
    public function findAll()
    {
        return $this->_em->createQuery('
		 	SELECT q
		 	FROM VidalBigMamaBundle:Question q
		 	ORDER BY q.enabled DESC, q.created DESC
		');
    }

    public function findActive()
    {
        return $this->_em->createQuery('
		 	SELECT q
		 	FROM VidalBigMamaBundle:Question q
			WHERE q.enabled = TRUE
				AND q.answer IS NOT NULL
				AND LENGTH(q.answer) > 0
			ORDER BY q.created DESC
		')->getResult();
    }
}