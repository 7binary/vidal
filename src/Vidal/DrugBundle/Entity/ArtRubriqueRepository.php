<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ArtRubriqueRepository extends EntityRepository
{
	public function findActive()
	{
		return $this->_em->createQuery('
			SELECT r
			FROM VidalDrugBundle:ArtRubrique r
			WHERE r.enabled = TRUE
			ORDER BY r.priority DESC, r.title ASC
		')->getResult();
	}

    public function findByIds($ids)
    {
        return $this->_em->createQuery('
			SELECT r
			FROM VidalDrugBundle:ArtRubrique r
			WHERE r.id IN (:ids)
		')->setParameter('ids', $ids)
            ->getResult();
    }

	public function findSitemap()
	{
		$raw = $this->_em->createQuery('
			SELECT a.title, a.link, a.id,
				r.title rubriqueTitle, r.url rubriqueUrl,
				t.title typeTitle, t.url typeUrl,
				c.title categoryTitle, c.url categoryUrl
			FROM VidalDrugBundle:Art a
			JOIN a.rubrique r
			LEFT JOIN a.type t
			LEFT JOIN a.category c
			ORDER BY r.title, t.title, c.title, a.title
		')->getResult();

		# запихиваем в группы
		$result = array();

		foreach ($raw as $r) {
			$rubrique = $r['rubriqueUrl'];

			if (!isset($result[$rubrique])) {
				$result[$rubrique] = array(
					'children' => array(),
					'articles' => array(),
					'title'    => $r['rubriqueTitle'],
					'url'      => $r['rubriqueUrl'],
				);
			}

			if ($r['categoryUrl'] === null && $r['typeUrl'] === null) {
				# статья в рубрике
				$result[$rubrique]['articles'][] = $r;
			}
			elseif ($r['categoryUrl'] === null) {
				# статья в типе
				$type = $r['typeUrl'];
				if (!isset($result[$rubrique]['children'][$type])) {
					$result[$rubrique]['children'][$type] = array(
						'children' => array(),
						'articles' => array(),
						'title'    => $r['typeTitle'],
						'url'      => $r['typeUrl'],
					);
				}
				$result[$rubrique]['children'][$type]['articles'][] = $r;
			}
			else {
				# статья в категории
				$type = $r['typeUrl'];
				if (!isset($result[$rubrique]['children'][$type])) {
					$result[$rubrique]['children'][$type] = array(
						'children' => array(),
						'articles' => array(),
						'title'    => $r['typeTitle'],
						'url'      => $r['typeUrl'],
					);
				}

				$category = $r['categoryUrl'];
				if (!isset($result[$rubrique]['children'][$type]['children'][$category])) {
					$result[$rubrique]['children'][$type]['children'][$category] = array(
						'articles' => array(),
						'title'    => $r['categoryTitle'],
						'url'      => $r['categoryUrl'],
					);

				}
				$result[$rubrique]['children'][$type]['children'][$category]['articles'][] = $r;
			}
		}

		return $result;
	}
}