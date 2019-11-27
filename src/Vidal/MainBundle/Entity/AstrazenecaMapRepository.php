<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AstrazenecaMapRepository extends EntityRepository
{
    public function findCoords($coords)
    {

        return $this->_em->createQueryBuilder()
            ->select('am')
            ->from('VidalMainBundle:AstrazenecaMap', 'am')
            ->where("am.latitude > $coords[0]")
            ->where("am.latitude < $coords[2]")
            ->andWhere("am.longitude > $coords[1]")
            ->andWhere("am.longitude < $coords[3]")
            ->getQuery()
            ->getResult();
    }
}