<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RiglaRepository extends EntityRepository
{
    public function findRegion($firstName, $secondName)
    {
        return $this->_em->createQuery('
            SELECT r
            FROM VidalDrugBundle:RiglaRegion r
            WHERE r.name LIKE :firstName
              OR r.name LIKE :secondName
        ')->setParameter('firstName', $firstName . '%')
            ->setParameter('secondName', $secondName . '%')
            ->getOneOrNullResult();
    }
    public function findPrice($ProductID, $regionId)
    {
        return $this->_em->createQuery("
            SELECT p
            FROM VidalDrugBundle:RiglaPrice p
            JOIN p.rigla r
            JOIN r.products product
            WHERE product.ProductID = :ProductID
              AND p.region = :regionId
              AND p.price NOT IN ('','0','0.0','0.00')
        ")->setParameter('ProductID', $ProductID )
            ->setParameter('regionId', $regionId)
            ->getResult();
    }
}