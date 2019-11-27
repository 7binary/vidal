<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ArticleCategoryRepository extends EntityRepository
{
	public function findByRubriqueLink($rubrique, $link)
	{
		return $this->_em->createQuery('
			SELECT c
			FROM VidalDrugBundle:ArticleCategory c
			LEFT JOIN c.rubrique r
			WHERE c.enabled = 1
			  AND r.rubrique = :rubrique
			  AND c.url = :link
			ORDER BY c.priority DESC, c.title ASC
		')->setParameter('rubrique', $rubrique)
            ->setParameter('link', $link)
			->getOneOrNullResult();
	}
}