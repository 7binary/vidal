<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DigestRepository extends EntityRepository
{
    public function getExcludedProducts()
    {
        /** @var Digest $digest */
        $digest = $this->get();

        $products = $digest->getExcludedProducts();
        $products = explode(';', $products);

        $result = array();

        foreach ($products as $p) {
            $result[] = trim(mb_strtolower($p, 'utf-8'));
        }

        return $result;
    }

    public function get($id = null)
    {
        if ($id) {
            $digest = $this->_em->createQuery('
                SELECT d
                FROM VidalMainBundle:Digest d
                WHERE d.id = :id
            ')->setParameter('id', $id)
                ->getOneOrNullResult();
        }
        else {
            $digest = $this->_em->createQuery('
                SELECT d
                FROM VidalMainBundle:Digest d
                ORDER BY d.id ASC
            ')->setMaxResults(1)
                ->getOneOrNullResult();
        }

        if (!$digest) {
            $digest = new Digest();
            $digest->setSubject('Тема письма');
            $digest->setText('<p>Текст письма</p>');
            $digest->setFont('Arial');
            if ($id) {
                $now = new \DateTime('now');
                $digest->setId($id);
                $uniqid = 'Delivery_' . $now->format('d.m.Y') . '_' . uniqid();
                $digest->setUniqueid($uniqid);
            }
            $this->_em->persist($digest);
            $this->_em->flush($digest);
            $this->_em->refresh($digest);
        }

        return $digest;
    }

    public function countSubscribed()
    {
        return $this->_em->createQuery('
			SELECT COUNT(u.id)
			FROM VidalMainBundle:User u
			WHERE u.digestSubscribed = TRUE
				AND u.enabled = TRUE
				AND (u.mail_delete_counter IS NULL OR u.mail_delete_counter <= 5)
		')->getSingleScalarResult();
    }

    public function countUnsubscribed()
    {
        $intervals = array('day', 'week', 'month', 'year');
        $unsub = array();

        foreach ($intervals as $interval) {
            $unsub[$interval] = $this->_em->createQuery("
				SELECT COUNT(u.id)
				FROM VidalMainBundle:User u
				WHERE u.digestSubscribed = FALSE
					AND u.enabled = TRUE
					AND u.emailConfirmed = TRUE
					AND u.digestUnsubscribed > '" . date('Y-m-d', strtotime("-1 $interval")) . "'"
            )->getSingleScalarResult();
        }

        return $unsub;
    }
}