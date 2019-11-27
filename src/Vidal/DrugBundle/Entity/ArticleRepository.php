<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Vidal\DrugBundle\Entity\Article;

class ArticleRepository extends EntityRepository
{
    public function ofRubrique($rubrique, $testMode = false, $invisible = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a')
            ->from('VidalDrugBundle:Article', 'a')
            ->where('a.rubrique = :rubriqueId')
            ->orderBy('a.listPriority', 'DESC')
            ->addOrderBy('a.title', 'ASC')
            ->setParameter('rubriqueId', $rubrique->getId());

        $testMode
            ? $qb->andWhere('a.enabled = TRUE OR a.testMode = TRUE')
            : $qb->andWhere('a.enabled = TRUE');

        if ($invisible == false) {
            $qb->andWhere('a.invisible = FALSE');
        }

        $qb->andWhere('a.id NOT IN (:ids)')->setParameter('ids', array(475, 455, 454, 476, 674, 399));

        return $qb->getQuery()->getResult();
    }

    public function findLast($testMode = false, $invisible = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a')
            ->from('VidalDrugBundle:Article', 'a')
            ->andWhere('a.date < :now')
            ->andWhere('a.anons = TRUE')
            ->orderBy('a.anonsPriority', 'DESC')
            ->addOrderBy('a.date', 'DESC')
            ->setMaxResults(3)
            ->setParameter('now', new \DateTime());

        $testMode
            ? $qb->andWhere('a.enabled = TRUE OR a.testMode = TRUE')
            : $qb->andWhere('a.enabled = TRUE');

        if ($invisible == false) {
            $qb->andWhere('a.invisible = FALSE');
        }

        return $qb->getQuery()->getResult();
    }

    public function findOneByRubriqueLink($rubrique_id, $link)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a')
            ->from('VidalDrugBundle:Article', 'a')
            ->where('a.rubrique = :rubriqueId')
            ->andWhere('a.link = :link')
            ->setMaxResults(1)
            ->setParameter('link', $link)
            ->setParameter('rubriqueId', $rubrique_id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findOneByCategoryLink($category_id, $link)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a')
            ->from('VidalDrugBundle:Article', 'a')
            ->where('a.category = :categoryId')
            ->andWhere('a.link = :link')
            ->setMaxResults(1)
            ->setParameter('link', $link)
            ->setParameter('categoryId', $category_id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findFrom($from, $max)
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:Article a
			WHERE a.enabled = TRUE
				AND a.date < :now
			ORDER BY a.priority DESC, a.date DESC
		')->setParameter('now', new \DateTime())
            ->setFirstResult($from)
            ->setMaxResults($max)
            ->getResult();
    }

    public function findDisease($l)
    {
        return $this->_em->createQuery('
			SELECT a.title, a.synonym, a.link, r.rubrique rubrique
			FROM VidalDrugBundle:Article a
			LEFT JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
				AND a.date < :now
				AND (a.title LIKE :l1 OR a.title LIKE :l2 OR a.synonym LIKE :l3 OR a.synonym LIKE :l4)
				AND a.title NOT LIKE :l5
				AND a.synonym NOT LIKE :l6
			ORDER BY a.title ASC
		')->setParameters(array(
            'now' => new \DateTime(),
            'l1' => $l . '%',
            'l2' => '% ' . $l . '%',
            'l3' => $l . '%',
            'l4' => '% ' . $l . '%',
            'l5' => '% ' . $l . ' %',
            'l6' => '% ' . $l . ' %',
        ))
            ->getResult();
    }

    public function findByQuery($q)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a.title, a.synonym, a.link, r.rubrique rubrique')
            ->from('VidalDrugBundle:Article', 'a')
            ->leftJoin('a.rubrique', 'r')
            ->where('a.enabled = TRUE')
            ->andWhere('r.enabled = TRUE')
            ->andWhere('a.date < :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('a.title', 'ASC');

        # поиск по словам
        $where = '';
        $words = explode(' ', $q);

        # находим все слова
        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' AND ';
            }
            $where .= "(a.title LIKE '$word%' OR a.title LIKE '% $word%' OR a.synonym LIKE '$word%' OR a.synonym LIKE '% $word%')";
        }

        $qb->andWhere($where);
        $articles = $qb->getQuery()->getResult();

        # находим какое-либо из слов, если нет результата
        if (empty($articles)) {
            foreach ($words as $word) {
                if (mb_strlen($word, 'utf-8') < 3) {
                    return array();
                }
            }

            $where = '';

            for ($i = 0; $i < count($words); $i++) {
                $word = $words[$i];
                if ($i > 0) {
                    $where .= ' OR ';
                }
                $where .= "(a.title LIKE '$word%' OR a.title LIKE '% $word%' OR a.synonym LIKE '$word%' OR a.synonym LIKE '% $word%')";
            }

            $qb->where($where)
                ->andWhere('a.enabled = TRUE')
                ->andWhere('r.enabled = TRUE')
                ->andWhere('a.date < :now')
                ->setParameter('now', new \DateTime());
            $articles = $qb->getQuery()->getResult();
        }

        return $articles;
    }

    public function findByRubriqueId($id)
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:Article a
			WHERE a.enabled = 1
				AND a.rubrique = :id
				AND a.date < :now
			ORDER BY a.title ASC
		')->setParameter('now', new \DateTime())
            ->setParameter('id', $id)
            ->getResult();
    }

    public function getQueryByTag($tagId)
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:Article a
			JOIN a.tags t
			WHERE a.enabled = 1
				AND a.date < :now
				AND t = :tagId
			ORDER BY a.title ASC
		')->setParameter('now', new \DateTime())
            ->setParameter('tagId', $tagId)
            ->getResult();
    }

    public function findByTagWord($tagId, $text)
    {
        if (empty($text)) {
            $results1 = $this->_em->createQuery('
				SELECT a
				FROM VidalDrugBundle:Article a
				JOIN a.tags t WITH t = :tagId
			')->setParameter('tagId', $tagId)
                ->getResult();

            $results2 = $this->_em->createQuery('
				SELECT a
				FROM VidalDrugBundle:Article a
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
            $ids = $tagHistory->getArticleIds();

            if (empty($ids)) {
                return array();
            }

            return $this->_em->createQuery('
				SELECT a
				FROM VidalDrugBundle:Article a
				WHERE a.id IN (:ids)
			')->setParameter('ids', $ids)
                ->getResult();
        }
    }

    public function findByNozology($nozologyCodes)
    {
        return $this->_em->createQuery('
			SELECT a
			FROM VidalDrugBundle:Article a
			JOIN a.nozologies n WITH n.NozologyCode IN (:codes)
			JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
			ORDER BY a.date DESC
		')->setParameter('codes', $nozologyCodes)
            ->getResult();
    }

    public function findActive()
    {
        return $this->_em->createQuery('
		 	SELECT a
		 	FROM VidalDrugBundle:Article a
		 	JOIN a.rubrique r
		 	WHERE a.enabled = TRUE
		 		AND r.enabled = TRUE
			ORDER BY a.title ASC
		')->getResult();
    }

    public function export()
    {
        $pdo = $this->_em->getConnection();

        $stmt = $pdo->prepare("
            SELECT a.id, a.title, a.date, a.announce, a.body, r.title rubriqueTitle, r.rubrique rubriqueLink, a.link, a.authors,
              (
                SELECT GROUP_CONCAT(CONCAT(n.NozologyCode, ' - ', n.Name) SEPARATOR '; ')
                FROM nozology n
                LEFT JOIN article_n an ON an.NozologyCode = n.NozologyCode
                WHERE an.article_id = a.id
                GROUP BY an.article_id
              ) as nozology
            FROM article a
            LEFT JOIN article_rubrique r ON r.id = a.rubrique_id
            WHERE a.disableExport != TRUE
        ");

        $stmt->execute();
        $articles = $stmt->fetchAll();

        return $articles;
    }

    public function articlesByAtc($atcCodes)
    {
        return $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.rubrique rubrique, r.title rubriqueTitle, atc.ATCCode atcTitle
			FROM VidalDrugBundle:Article a
			JOIN a.atcCodes atc WITH atc.ATCCode IN (:atcCodes)
			JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
			ORDER BY a.date DESC
		')->setParameter('atcCodes', $atcCodes)
            ->getResult();
    }

    public function articlesByNosology($nosologyCodes)
    {
        return $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.rubrique rubrique, r.title rubriqueTitle, n.NozologyCode nozologyCode
			FROM VidalDrugBundle:Article a
			JOIN a.nozologies n WITH (n.NozologyCode IN (:nosologyCodes) OR n.Code IN (:nosologyCodes))
			JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
			ORDER BY a.date DESC
		')->setParameter('nosologyCodes', $nosologyCodes)
            ->getResult();
    }

    public function findNextArticle(Article $article)
    {
        return $this->findNext($article);
    }

    public function findPrevArticle(Article $article)
    {
        return $this->findPrev($article);
    }

    /**
     * Находит предыдущу статью в рубрике
     * 
     * @access protected
     * @param Article $article
     * @return null|Article
     */
    protected function findPrev(Article $article)
    {
        if ($article->getRubrique()) {
            $prevs = $this->_em->createQuery("
                SELECT a
                FROM VidalDrugBundle:Article a
                WHERE a.enabled = TRUE
                    AND a.invisible = FALSE
                    AND a.rubrique = :rubrique
                ORDER BY a.listPriority DESC, a.title ASC
            ")
                ->setParameter('rubrique', $article->getRubrique()->getId())
                ->setMaxResults(100)
                ->getResult();
                $isPrev = false;
                foreach($prevs as $prev) {
                    if ($prev->getId() == $article->getId()) {
                        return $isPrev;
                    }
                    $isPrev = $prev;
                }
        }
        return null;
    }

    /**
     * Находит следующую статью в рубрике
     * 
     * @access protected
     * @param Article $article
     * @return null|Article
     */
    protected function findNext(Article $article)
    {
        if ($article->getRubrique()) {
            $nexts =  $this->_em->createQuery("
                SELECT a
                FROM VidalDrugBundle:Article a
                WHERE a.enabled = TRUE
                    AND a.rubrique = :rubrique
                    AND a.invisible = FALSE
                ORDER BY a.listPriority DESC, a.title ASC
            ")
                ->setParameter('rubrique', $article->getRubrique()->getId())
                ->setMaxResults(100)
                ->getResult();

                $isNext = false;
                foreach($nexts as $next) {
                    if ($next->getId() == $article->getId()) {
                        $isNext = true;
                        continue;
                    }
                    if($isNext) {
                        return $next;
                    }
                }
        }
        return null;
    }
}