<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function findActive()
    {
        return $this->_em->createQuery('
		 	SELECT c
		 	FROM VidalBigMamaBundle:Category c
		 	WHERE c.enabled = TRUE
		 	ORDER BY c.position ASC, c.id ASC
		')->getResult();
    }
}