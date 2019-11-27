<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PublicationRepository extends EntityRepository
{
    public function findMoreNews($currPage, $limit = 10, $project = null)
    {
        if ($project) {
            return $this->_em->createQuery("
                SELECT p
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE AND p.priority IS NULL AND p.project = :project
                ORDER BY p.date DESC
            ")->setParameter('project', $project)
                ->setFirstResult($currPage * $limit)
                ->setMaxResults($limit)
                ->getResult();
        }

        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE AND p.priority IS NULL AND (p.project IS NULL OR p.project = '')
			ORDER BY p.date DESC
		")->setFirstResult($currPage * $limit)
            ->setMaxResults($limit)
            ->getResult();
    }

    public function findRandomPublications($id, $qty = 5, $project = null)
    {
        if ($project) {
            $all = $this->_em->createQuery("
                SELECT p.id
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE AND p.id != :id AND p.project = :project
            ")->setParameter('project', $project)
                ->setParameter('id', $id)
                ->getResult();
        }
        else {
            $all = $this->_em->createQuery("
                SELECT p.id
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE AND p.id != :id AND (p.project IS NULL OR p.project = '')
            ")->setParameter('id', $id)
                ->getResult();
        }

        $allIds = array();

        foreach ($all as $p) {
            $allIds[] = $p['id'];
        }

        shuffle($allIds);

        $ids = array_splice($allIds, 0, $qty);

        return $this->_em->createQuery('
			SELECT p
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE AND p.id IN (:ids)
		')->setParameter('ids', $ids)
            ->getResult();
    }

    public function findNextPublication($id, $project = null)
    {
        $next = $this->findNext($id, $project);

        return $next ? $next : $this->findFirstOfAll($project);
    }

    public function findPrevPublication($id, $project = null)
    {
        $prev = $this->findPrev($id, $project);

        return $prev ? $prev : $this->findLastOfAll($project);
    }

    private function findFirstOfAll($project = null)
    {
        if ($project) {
            return $this->_em->createQuery('
                SELECT p
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE AND p.project = :project
                ORDER BY p.id ASC
            ')->setParameter('project', $project)->setMaxResults(1)->getOneOrNullResult();
        }

        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE AND (p.project IS NULL OR p.project = '')
			ORDER BY p.id ASC
		")->setMaxResults(1)->getOneOrNullResult();
    }

    private function findLastOfAll($project = null)
    {
        if ($project) {
            return $this->_em->createQuery("
                SELECT p
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE AND p.project = :project
                ORDER BY p.id DESC
            ")->setParameter('project', $project)->setMaxResults(1)->getOneOrNullResult();
        }

        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE AND (p.project IS NULL OR p.project = '')
			ORDER BY p.id DESC
		")->setMaxResults(1)->getOneOrNullResult();
    }

    private function findPrev($id, $project = null)
    {
        if ($project) {
            return $this->_em->createQuery("
                SELECT p
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE
                    AND p.project = :project
                    AND p.id < :id
                ORDER BY p.id DESC
            ")->setParameter('id', $id)
                ->setParameter('project', $project)
                ->setMaxResults(1)
                ->getOneOrNullResult();
        }

        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE
			    AND (p.project IS NULL OR p.project = '')
				AND p.id < :id
			ORDER BY p.id DESC
		")->setParameter('id', $id)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    private function findNext($id, $project = null)
    {
        if ($project) {
            return $this->_em->createQuery("
                SELECT p
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE
                    AND p.id > :id
                    AND p.project = :project
                ORDER BY p.id ASC
            ")->setParameter('id', $id)
                ->setParameter('project', $project)
                ->setMaxResults(1)
                ->getOneOrNullResult();
        }

        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE
				AND p.id > :id
				AND (p.project IS NULL OR p.project = '')
			ORDER BY p.id ASC
		")->setParameter('id', $id)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findLast($top = 4, $testMode = false, $invisible = false, $project = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from('VidalDrugBundle:Publication', 'p')
            ->andWhere('p.date < :now')
            ->andWhere('p.priority IS NULL')
            ->addOrderBy('p.sticked', 'DESC')
            ->addOrderBy('p.date', 'DESC')
            ->setParameter('now', new \DateTime())
            ->setMaxResults($top);

        $project
            ? $qb->andWhere('p.project = :project')->setParameter('project', $project)
            : $qb->andWhere("(p.project IS NULL OR p.project = '')");

        $testMode
            ? $qb->andWhere('p.enabled = TRUE OR p.testMode = TRUE')
            : $qb->andWhere('p.enabled = TRUE');

        if ($invisible == false) {
            $qb->andWhere('p.invisible = FALSE');
        }

        return $qb->getQuery()->getResult();
    }

    public function findLastPriority($top = 3, $testMode = false, $project = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from('VidalDrugBundle:Publication', 'p')
            ->andWhere('p.date < :now')
            ->andWhere('p.priority IS NOT NULL')
            ->orderBy('p.priority', 'DESC')
            ->addOrderBy('p.date', 'DESC')
            ->setParameter('now', new \DateTime())
            ->setMaxResults($top);

        if ($project) {
            $qb->andWhere("p.project = :project")->setParameter('project', $project);
        }
        else {
            $qb->andWhere("(p.project IS NULL OR p.project = '')");
        }

        $testMode
            ? $qb->andWhere('p.enabled = TRUE OR p.testMode = TRUE')
            : $qb->andWhere('p.enabled = TRUE');

        return $qb->getQuery()->getResult();
    }

    public function findFrom($from, $max, $project = null)
    {
        if ($project) {
            return $this->_em->createQuery('
                SELECT p
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE
                    AND p.date < :now
                    AND p.project
                ORDER BY p.priority DESC, p.date DESC
            ')->setParameter('now', new \DateTime())
                ->setParameter('project', $project)
                ->setFirstResult($from)
                ->setMaxResults($max)
                ->getResult();
        }

        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE
				AND p.date < :now
				AND (p.project IS NULL OR p.project = '')
			ORDER BY p.priority DESC, p.date DESC
		")->setParameter('now', new \DateTime())
            ->setFirstResult($from)
            ->setMaxResults($max)
            ->getResult();
    }

    public function getQueryEnabled($testMode = false, $invisible = false, $project = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from('VidalDrugBundle:Publication', 'p')
            ->andWhere('p.date < :now')
            ->andWhere('p.priority IS NULL')
            ->addOrderBy('p.date', 'DESC')
            ->setParameter('now', new \DateTime());

        if ($project) {
            $qb->andWhere("p.project = :project")->setParameter('project', $project);
        }
        else {
            $qb->andWhere("(p.project IS NULL OR p.project = '')");
        }

        $testMode
            ? $qb->andWhere('p.enabled = TRUE OR p.testMode = TRUE')
            : $qb->andWhere('p.enabled = TRUE');

        if ($invisible == false) {
            $qb->andWhere('p.invisible = FALSE');
        }

        return $qb->getQuery();
    }

    public function getQueryByTag($tagId)
    {
        return $this->_em->createQuery('
			SELECT p
			FROM VidalDrugBundle:Publication p
			JOIN p.tags t
			WHERE p.enabled = TRUE
				AND p.date < :now
				AND t = :tagId
			ORDER BY p.priority DESC, p.date DESC
		')->setParameter('now', new \DateTime())
            ->setParameter('tagId', $tagId);
    }

    public function findByTagWord($tagId, $text)
    {
        if (empty($text)) {
            $results = array();

            $results1 = $this->_em->createQuery('
				SELECT p
				FROM VidalDrugBundle:publication p
				JOIN p.tags t WITH t = :tagId
			')->setParameter('tagId', $tagId)
                ->getResult();

            $results2 = $this->_em->createQuery('
				SELECT p
				FROM VidalDrugBundle:publication p
				JOIN p.infoPages i
				JOIN i.tag t WITH t = :tagId
			')->setParameter('tagId', $tagId)
                ->getResult();

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
            $ids = $tagHistory->getPublicationIds();

            if (empty($ids)) {
                return array();
            }

            return $this->_em->createQuery('
				SELECT p
				FROM VidalDrugBundle:Publication p
				WHERE p.id IN (:ids)
			')->setParameter('ids', $ids)
                ->getResult();
        }
    }

    public function findByNozology($nozologyCodes, $project = null)
    {
        if ($project) {
            return $this->_em->createQuery("
                SELECT p
                FROM VidalDrugBundle:Publication p
                JOIN p.nozologies n WITH n.NozologyCode IN (:codes)
                WHERE p.enabled = TRUE AND p.project = :project
                ORDER BY p.date DESC
            ")->setParameter('project', $project)
                ->setParameter('codes', $nozologyCodes)
                ->getResult();
        }

        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			JOIN p.nozologies n WITH n.NozologyCode IN (:codes)
			WHERE p.enabled = TRUE AND (p.project IS NULL OR p.project = '')
			ORDER BY p.date DESC
		")->setParameter('codes', $nozologyCodes)
            ->getResult();
    }

    public function findByMkb($nozologyCodes, $project = null)
    {
        if ($project) {
            return $this->_em->createQuery("
                SELECT p.id, p.title, p.link
                FROM VidalDrugBundle:Publication p
                JOIN p.nozologies n WITH n.NozologyCode IN (:codes)
                WHERE p.enabled = TRUE AND p.project = :project
                ORDER BY p.date DESC
            ")->setParameter('project', $project)
                ->setParameter('codes', $nozologyCodes)
                ->getResult();
        }

        return $this->_em->createQuery("
			SELECT p.id, p.title, p.link
			FROM VidalDrugBundle:Publication p
			JOIN p.nozologies n WITH n.NozologyCode IN (:codes)
			WHERE p.enabled = TRUE AND (p.project IS NULL OR p.project = '')
			ORDER BY p.date DESC
		")->setParameter('codes', $nozologyCodes)
            ->getResult();
    }

    public function findByAtc($atcCodes, $project = null)
    {
        if ($project) {
            return $this->_em->createQuery("
                SELECT p.id, p.title, p.link
                FROM VidalDrugBundle:Publication p
                JOIN p.atcCodes atc WITH atc.ATCCode IN (:atcCodes)
                WHERE p.enabled = TRUE AND p.project = :project
                ORDER BY p.date DESC
            ")->setParameter('project', $project)
                ->setParameter('atcCodes', $atcCodes)
                ->getResult();
        }

        return $this->_em->createQuery("
			SELECT p.id, p.title, p.link
			FROM VidalDrugBundle:Publication p
			JOIN p.atcCodes atc WITH atc.ATCCode IN (:atcCodes)
			WHERE p.enabled = TRUE AND (p.project IS NULL OR p.project = '')
			ORDER BY p.date DESC
		")->setParameter('atcCodes', $atcCodes)
            ->getResult();
    }

    public function findLeft($max = 5, $project = null)
    {
        if ($project) {
            $sticked = $this->_em->createQuery("
                SELECT p.id, p.title, p.date, p.announce, p.sticked, p.link
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE
                    AND p.sticked = TRUE
                    AND p.project = :project
                ORDER BY p.date DESC
            ")->setParameter('project', $project)->getResult();

            $fresh = $this->_em->createQuery("
                SELECT p.id, p.title, p.date, p.announce, p.sticked, p.link
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE
                    AND p.date < :now
                    AND p.sticked = FALSE
                    AND p.project = :project
                ORDER BY p.date DESC
            ")->setParameter('project', $project)
                ->setParameter('now', new \DateTime())
                ->setMaxResults($max)
                ->getResult();
        }
        else {
            $sticked = $this->_em->createQuery("
                SELECT p.id, p.title, p.date, p.announce, p.sticked, p.link
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE
                    AND p.sticked = TRUE
                    AND (p.project IS NULL OR p.project = '')
                ORDER BY p.date DESC
            ")->getResult();

            $fresh = $this->_em->createQuery("
                SELECT p.id, p.title, p.date, p.announce, p.sticked, p.link
                FROM VidalDrugBundle:Publication p
                WHERE p.enabled = TRUE
                    AND p.date < :now
                    AND p.sticked = FALSE
                    AND (p.project IS NULL OR p.project = '')
                ORDER BY p.date DESC
            ")->setParameter('now', new \DateTime())
                ->setMaxResults($max)
                ->getResult();
        }

        return array_merge($sticked, $fresh);
    }

    public function findForApi($from, $size, $project = null)
    {
        if ($project) {
            $publications = $this->_em->createQuery("
                SELECT p.title, p.announce, p.date, p.id, p.link
                FROM VidalDrugBundle:Publication p
                INNER JOIN p.categories c
                WHERE p.enabled = TRUE
                    AND p.mobile = TRUE
                    AND c.project = :project
                ORDER BY p.priority DESC, p.date DESC
            ")->setParameter('project', $project)
                ->setMaxResults($size)
                ->setFirstResult($from)
                ->getResult();
        }
        else {
            $publications = $this->_em->createQuery("
                SELECT p.title, p.announce, p.date, p.id, p.link
                FROM VidalDrugBundle:Publication p
                LEFT JOIN p.categories c
                WHERE p.enabled = TRUE
                    AND p.mobile = TRUE
                    AND (c.project IS NULL OR c.project != 'veterinary')
                ORDER BY p.priority DESC, p.date DESC
            ")->setMaxResults($size)
                ->setFirstResult($from)
                ->getResult();
        }

        for ($i = 0; $i < count($publications); $i++) {
            $publications[$i]['date'] = $publications[$i]['date']->format('Y-m-d H:i:s');
        }

        return $publications;
    }

    public function findForApiById($id)
    {
        $publication = $this->_em->createQuery('
			SELECT p.title, p.announce, p.body, p.bodyMobile, p.date, p.id, p.link
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE
				AND p.mobile = TRUE
				AND p.id = :id
		')->setParameter('id', $id)
            ->getOneOrNullResult();

        if (empty($publication)) {
            return array();
        }

        $publication['date'] = $publication['date']->format('Y-m-d H:i:s');

        return $publication;
    }

    public function findRawForApi($from, $size, $project = null)
    {
        $publications = $this->findForApi($from, $size, $project);

        for ($i = 0; $i < count($publications); $i++) {
            $publications[$i]['title'] = strip_tags($publications[$i]['title']);
            $publications[$i]['announce'] = strip_tags($publications[$i]['announce']);
        }

        return $publications;
    }

    public function findRawForApiById($id)
    {
        $publication = $this->_em->createQuery('
			SELECT p.title, p.announce, p.body, p.date, p.id, p.link
			FROM VidalDrugBundle:Publication p
			WHERE p.enabled = TRUE
				AND p.mobile = TRUE
				AND p.id = :id
		')->setParameter('id', $id)
            ->getOneOrNullResult();

        if (empty($publication)) {
            return array();
        }

        $publication['date'] = $publication['date']->format('Y-m-d H:i:s');
        $publication['title'] = strip_tags($publication['title']);
        $publication['announce'] = strip_tags($publication['announce']);
        $publication['body'] = strip_tags($publication['body']);

        return $publication;
    }
}