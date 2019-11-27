<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ArtRepository extends EntityRepository
{
    public function getQueryByRubrique($rubrique, $invisible = false)
    {
        if ($invisible) {
            return $this->_em->createQuery('
                SELECT a
                FROM VidalDrugBundle:Art a
                WHERE a.rubrique = :id
                    AND a.type IS NULL
                    AND a.enabled = TRUE
                    AND a.date < :now
                ORDER BY a.listPriority DESC, a.date DESC, a.priority DESC
            ')->setParameter('now', new \DateTime())
                ->setParameter('id', $rubrique->getId());
        }
        else {
            return $this->_em->createQuery('
                SELECT a
                FROM VidalDrugBundle:Art a
                WHERE a.rubrique = :id
                    AND a.type IS NULL
                    AND a.enabled = TRUE
                    AND a.invisible = false
                    AND a.date < :now
                ORDER BY a.listPriority DESC, a.date DESC, a.priority DESC
            ')->setParameter('now', new \DateTime())
                ->setParameter('id', $rubrique->getId());
        }
    }

    public function artsByAtc($atcCodes)
    {
        return $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.url rubriqueUrl, r.title rubriqueTitle,
				c.url categoryUrl, t.url typeUrl, atc.ATCCode atcTitle
			FROM VidalDrugBundle:Art a
			JOIN a.atcCodes atc WITH atc.ATCCode IN (:atcCodes)
			JOIN a.rubrique r
			LEFT JOIN a.category c
			LEFT JOIN a.type t
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
				AND (t.id IS NULL OR t.enabled = TRUE)
				AND (c.id IS NULL OR c.enabled = TRUE)
			ORDER BY a.date DESC
		')->setParameter('atcCodes', $atcCodes)
            ->getResult();
    }

    public function artsByMkb($codes)
    {
        return $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.url rubriqueUrl, r.title rubriqueTitle,
				c.url categoryUrl, t.url typeUrl
			FROM VidalDrugBundle:Art a
			JOIN a.nozologies n WITH (n.NozologyCode IN (:codes) OR n.Code IN (:codes))
			JOIN a.rubrique r
			LEFT JOIN a.category c
			LEFT JOIN a.type t
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
				AND (t.id IS NULL OR t.enabled = TRUE)
				AND (c.id IS NULL OR c.enabled = TRUE)
			ORDER BY a.date DESC
		')->setParameter('codes', $codes)
            ->getResult();
    }

    public function getQueryByType($type, $invisible = false)
    {
        if (!$type) {
            return null;
        }

        if ($invisible) {
            return $this->_em->createQuery('
                SELECT a
                FROM VidalDrugBundle:Art a
                WHERE a.type = :id
                    AND a.category IS NULL
                    AND a.enabled = TRUE
                    AND a.date < :now
                ORDER BY a.listPriority DESC, a.date DESC, a.priority DESC
            ')->setParameter('now', new \DateTime())
                ->setParameter('id', $type->getId());
        }
        else {
            return $this->_em->createQuery('
                SELECT a
                FROM VidalDrugBundle:Art a
                WHERE a.type = :id
                    AND a.category IS NULL
                    AND a.enabled = TRUE
                    AND a.invisible = FALSE
                    AND a.date < :now
                ORDER BY a.listPriority DESC, a.date DESC, a.priority DESC
            ')->setParameter('now', new \DateTime())
                ->setParameter('id', $type->getId());
        }
    }

    public function getQueryByCategory($category, $invisible = false)
    {
        if (!$category) {
            return null;
        }

        if ($invisible) {
            return $this->_em->createQuery('
                SELECT a
                FROM VidalDrugBundle:Art a
                WHERE a.category = :id
                    AND a.enabled = TRUE
                    AND a.date < :now
                ORDER BY a.listPriority DESC, a.date DESC, a.priority DESC
            ')->setParameter('now', new \DateTime())
                ->setParameter('id', $category->getId());
        }
        else {
            return $this->_em->createQuery('
                SELECT a
                    FROM VidalDrugBundle:Art a
                    WHERE a.category = :id
                        AND a.enabled = TRUE
                        AND a.invisible = FALSE
                        AND a.date < :now
                    ORDER BY a.listPriority DESC, a.date DESC, a.priority DESC
                ')->setParameter('now', new \DateTime())
                ->setParameter('id', $category->getId());
        }
    }

    public function findOneByRubriqueLink($rubriqueId, $link)
    {
        return $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalDrugBundle:Art a
		 	LEFT JOIN a.rubrique r
		 	WHERE a.enabled = TRUE
		 	    AND r.enabled = TRUE
		 	    AND r.id = :rubriqueId
		 	    AND a.link = :link
		 	ORDER BY a.date DESC
		')->setParameter('rubriqueId', $rubriqueId)
            ->setParameter('link', $link)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function atIndex()
    {
        return $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalDrugBundle:Art a
		 	WHERE a.atIndex = TRUE
		 		AND a.date < :now
		 	ORDER BY a.date DESC
		')->setParameter('now', new \DateTime())
            ->setMaxResults(2)
            ->getResult();
    }

    public function findForAnons()
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:Art a
			WHERE a.anons = TRUE
				AND a.enabled = TRUE
				AND a.date < :now
			ORDER BY a.anonsPriority DESC, a.date DESC
		')->setParameter('now', new \DateTime())
            ->getResult();
    }

    public function getQueryByTag($tagId)
    {
        return $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalDrugBundle:Art a
		 	JOIN a.tags t
		 	WHERE a.date < :now
		 		AND t = :tagId
		 	ORDER BY a.date DESC
		')->setParameter('now', new \DateTime())
            ->setParameter('tagId', $tagId)
            ->getResult();
    }

    public function findByTagWord($tagId, $text)
    {
        if (empty($text)) {
            $results1 = $this->_em->createQuery('
				SELECT a
				FROM VidalDrugBundle:Art a
				JOIN a.tags t WITH t = :tagId
			')->setParameter('tagId', $tagId)
                ->getResult();

            $results2 = $this->_em->createQuery('
				SELECT a
				FROM VidalDrugBundle:Art a
				JOIN a.infoPages i
				JOIN i.tag t WITH t = :tagId
			')->setParameter('tagId', $tagId)
                ->getResult();

            $results = array();

            foreach ($results1 as $r) {
                $key = $r->getId();
                $results[$key] = $r;
            }
            foreach ($results2 as $r) {
                $key = $r->getId();
                if (!isset($results[$key])) {
                    $results[$key] = $r;
                }
            }

            return array_values($results);
        }
        else {
            $tagHistory = $this->_em->getRepository('VidalDrugBundle:TagHistory')->findOneByTagText($tagId, $text);
            $ids = $tagHistory->getArtIds();

            if (empty($ids)) {
                return array();
            }

            return $this->_em->createQuery('
				SELECT a
				FROM VidalDrugBundle:Art a
				WHERE a.id IN (:ids)
			')->setParameter('ids', $ids)
                ->getResult();
        }
    }

    public function findByNozology($nozologyCodes)
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:Art a
			JOIN a.nozologies n WITH n.NozologyCode IN (:codes)
			JOIN a.rubrique r
			WHERE a.enabled = TRUE AND r.enabled = TRUE
			ORDER BY a.listPriority DESC, a.date DESC
		')->setParameter('codes', $nozologyCodes)
            ->getResult();
    }
}