<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DeliveryLogRepository extends EntityRepository
{
    public function findByDeliveryName($deliveryName)
    {
        return $this->_em->createQuery('
		 	SELECT dl
		 	FROM VidalMainBundle:DeliveryLog dl
		 	WHERE dl.uniqueid = :deliveryName
		 	ORDER BY dl.created ASC
		')->setParameter('deliveryName', $deliveryName)
            ->getArrayResult();
    }

    public function total($deliveryName)
    {
        $real = $this->_em->createQuery('
		 	SELECT COUNT(DISTINCT dl.email)
		 	FROM VidalMainBundle:DeliveryLog dl
		 	WHERE dl.uniqueid = :deliveryName
		 	  AND dl.fake = FALSE
		')->setParameter('deliveryName', $deliveryName)
            ->getSingleScalarResult();

        $faked = $this->_em->createQuery('
		 	SELECT COUNT(dl.id)
		 	FROM VidalMainBundle:DeliveryLog dl
		 	WHERE dl.uniqueid = :deliveryName
		 	  AND dl.fake = TRUE
		')->setParameter('deliveryName', $deliveryName)
            ->getSingleScalarResult();

        return $real + $faked;
    }

    public function days($deliveryName)
    {
        $pdo = $this->_em->getConnection();

        $stmt = $pdo->prepare("SELECT COUNT(id) `value`, DATE_FORMAT(created, '%Y-%m-%d %H') `date` FROM delivery_log WHERE uniqueid = '$deliveryName' GROUP BY DATE_FORMAT(created, '%Y-%m-%d %H')");
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $return = [];

        foreach ($rows as $r) {
            $return[] = ['value' => $r['value'], 'date' => $r['date'] . ':00'];
        }

        return $return;
    }
}