<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProtecPriceRepository extends EntityRepository
{
    /**
     * @param $region_id
     * @param $product_id
     * @return ProtecPrice
     */
    public function get($region_id, $product_id)
    {
        return $this->_em->createQuery("
            SELECT p
            FROM VidalMainBundle:ProtecPrice p
            WHERE p.region = :region_id AND p.product = :product_id
        ")->setParameter('region_id', $region_id)
            ->setParameter('product_id', $product_id)
            ->getOneOrNullResult();
    }
}