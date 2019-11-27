<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\EntityRepository;

class VideoRepository extends EntityRepository
{
    public function findMoreNews($currPage, $limit = 10)
    {
        return $this->_em->createQuery("
            SELECT p
            FROM VidalBigMamaBundle:Video p
            WHERE p.enabled = TRUE
            ORDER BY p.position ASC, p.date DESC
        ")->setFirstResult($currPage * $limit)
            ->setMaxResults($limit)
            ->getResult();
    }

    public function findActive($limit = 5)
    {
        $qb = $this->_em->createQuery('
             SELECT v
             FROM VidalBigMamaBundle:Video v
             WHERE v.enabled = TRUE
             ORDER BY v.position ASC, v.date DESC
        ');
        if($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getResult();
    }

    public function findLast($top = 3, $testMode = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from('VidalBigMamaBundle:Video', 'p')
            ->andWhere('p.enabled = TRUE')
            ->orderBy('p.position', 'DESC')
            ->addOrderBy('p.date', 'DESC')
            ->setMaxResults($top);

        return $qb->getQuery()->getResult();
    }
}