<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DigestOpenedRepository extends EntityRepository
{
	public function findByDeliveryName($deliveryName)
	{
        return $this->_em->createQuery('
		 	SELECT do
		 	FROM VidalMainBundle:DigestOpened do
		 	WHERE do.uniqueid = :deliveryName
		 	ORDER BY do.created ASC
		')->setParameter('deliveryName', $deliveryName)
            ->getArrayResult();
	}

    public function total($deliveryName)
    {
        return $this->_em->createQuery('
		 	SELECT COUNT(do.id)
		 	FROM VidalMainBundle:DigestOpened do
		 	WHERE do.uniqueid = :deliveryName
		 	ORDER BY do.created ASC
		')->setParameter('deliveryName', $deliveryName)
            ->getSingleScalarResult();
    }

    public function days($deliveryName)
    {
        $pdo = $this->_em->getConnection();

        $stmt = $pdo->prepare("SELECT COUNT(id) `value`, DATE_FORMAT(created, '%Y-%m-%d') `date` FROM digest_opened WHERE uniqueid = '$deliveryName' GROUP BY DATE_FORMAT(created, '%Y-%m-%d')");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return $rows;
    }

    public function hour($deliveryName)
    {
        $pdo = $this->_em->getConnection();

        $stmt = $pdo->prepare("SELECT COUNT(id) `value`, DATE_FORMAT(created, '%Y-%m-%d %H') `date` FROM digest_opened WHERE uniqueid = '$deliveryName' GROUP BY DATE_FORMAT(created, '%Y-%m-%d %H')");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $return = [];

        foreach ($rows as $r) {
            $return[] = ['value' => $r['value'], 'date' => $r['date'] . ':00'];
        }

        return $return;
    }
}