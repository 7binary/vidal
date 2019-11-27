<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ArticleRubriqueRepository extends EntityRepository
{
	public function findEnabledByRubrique($rubrique)
	{
		return $this->_em->createQuery('
			SELECT r
			FROM VidalDrugBundle:ArticleRubrique r
			WHERE r.rubrique = :rubrique
				AND r.enabled = TRUE
		')->setParameter('rubrique', $rubrique)
			->getOneOrNullResult();
	}

    public function findByIds($ids)
    {
        return $this->_em->createQuery('
			SELECT r
			FROM VidalDrugBundle:ArticleRubrique r
			WHERE r.id IN (:ids)
		')->setParameter('ids', $ids)
            ->getResult();
    }

	public function getByTitle($title, $category)
	{
		$rubrique = $this->_em->createQuery('
			SELECT r
			FROM VidalDrugBundle:ArticleRubrique r
			WHERE r.title = :title
		')->setParameter('title', $title)
			->getOneOrNullResult();

		if (empty($rubrique)) {
			$rubrique = new ArticleRubrique();
			$rubrique->setTitle($title);

			if (!empty($category)) {
				$rubrique->setRubrique($category);
			}

			$this->_em->persist($rubrique);
			$this->_em->flush($rubrique);
			$this->_em->refresh($rubrique);
		}

		return $rubrique;
	}

	public function findEnabled($testMode)
	{
	    if ($testMode) {
            return $this->_em->createQuery('
                SELECT r
                FROM VidalDrugBundle:ArticleRubrique r
                WHERE r.enabled = TRUE OR r.testMode = TRUE
                ORDER BY r.priority DESC, r.title ASC
            ')->getResult();
        }
        else {
            return $this->_em->createQuery('
                SELECT r
                FROM VidalDrugBundle:ArticleRubrique r
                WHERE r.enabled = TRUE AND r.testMode = FALSE
                ORDER BY r.priority DESC, r.title ASC
            ')->getResult();
        }
	}

	public function findSitemap()
	{
		$raw = $this->_em->createQuery('
			SELECT a.title, a.link, r.title rubriqueTitle, r.rubrique rubriqueLink
			FROM VidalDrugBundle:Article a
			JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
			ORDER BY r.title, a.title
		')->getResult();

		$rubriques = array();

		foreach ($raw as $r) {
			$key = $r['rubriqueLink'];
			if (isset($rubriques[$key])) {
				$rubriques[$key]['articles'][] = $r;
			}
			else {
				$rubriques[$key]['articles'] = array($r);
				$rubriques[$key]['rubriqueLink'] = $r['rubriqueLink'];
				$rubriques[$key]['rubriqueTitle'] = $r['rubriqueTitle'];
			}
		}

		return $rubriques;
	}
}