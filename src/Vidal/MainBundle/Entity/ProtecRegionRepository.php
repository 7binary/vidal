<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProtecRegionRepository extends EntityRepository
{
    /**
     * @param $regionTitle
     * @return ProtecRegion
     */
    public function get($regionTitle)
    {
        $region = $this->_em->createQuery("
            SELECT r 
            FROM VidalMainBundle:ProtecRegion r
            WHERE r.title LIKE :regionTitle
              OR r.title2 LIKE :regionTitle2
              OR r.title3 LIKE :regionTitle3
        ")->setParameter('regionTitle', $regionTitle)
            ->setParameter('regionTitle2', $regionTitle)
            ->setParameter('regionTitle3', $regionTitle)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return $region;
    }
}