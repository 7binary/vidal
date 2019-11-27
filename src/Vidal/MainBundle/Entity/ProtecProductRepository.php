<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Vidal\DrugBundle\Entity\Product;

class ProtecProductRepository extends EntityRepository
{
    /**
     * @param ProtecRegion $region
     * @param array $productIds
     * @return array
     */
    public function getByRegionProductID(ProtecRegion $region, $productIds)
    {
        return $this->_em->createQuery("
            SELECT p.id, p.title, p.form, pp.price, pp.link, r.title regionTitle, r.title2 regionTitle2
            FROM VidalMainBundle:ProtecPrice pp
            INNER JOIN pp.product p WITH p.ProductID IN (:productIds)
            INNER JOIN pp.region r WITH pp.region = :regionId
            WHERE pp.link IS NOT NULL AND pp.link != ''
              AND pp.price IS NOT NULL AND pp.price != ''
        ")->setParameter('regionId', $region->getId())
            ->setParameter('productIds', $productIds)
            ->getResult();
    }
}