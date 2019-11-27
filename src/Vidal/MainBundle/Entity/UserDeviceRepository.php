<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserDeviceRepository extends EntityRepository
{
    public function findByEmails($emails, $ios)
	{
		$raw = $this->_em->createQuery("
		 	SELECT ud.androidId, u.id userId
		 	FROM VidalMainBundle:UserDevice ud
		 	JOIN ud.user u
		 	WHERE ud.androidId IS NOT NULL
		 	  AND ud.androidId != ''
		 	  AND u.username IN (:emails)
		 	  AND ud.project = 'cardio'
		 	  AND ud.ios = :ios
		 	ORDER BY u.id ASC, ud.id DESC
		")
			->setParameter('emails', $emails)
			->setParameter('ios', $ios ? '1' : '0')
			->getResult();

		$ids = array();

		foreach ($raw as $r) {
			$userId = $r['userId'];

			if (!isset($ids[$userId])) {
				$ids[$userId] = $r['androidId'];
			}
		}

		return array_values($ids);
	}

	public function findNeuroByEmails($emails, $ios)
	{
	    return $this->devicesByEmails(UserDevice::PROJECT_NEURO, $emails, $ios);
	}

    public function devicesByEmails($projectName, $emails, $ios)
    {
        $raw = $this->_em->createQuery("
		 	SELECT ud.androidId, u.id userId
		 	FROM VidalMainBundle:UserDevice ud
		 	JOIN ud.user u
		 	WHERE ud.androidId IS NOT NULL
		 	  AND ud.androidId != ''
		 	  AND u.username IN (:emails)
		 	  AND ud.project = :projectName
		 	  AND ud.ios = :ios
		 	ORDER BY u.id ASC, ud.id DESC
		")
            ->setParameter('projectName', $projectName)
            ->setParameter('emails', $emails)
            ->setParameter('ios', $ios ? '1' : '0')
            ->getResult();

        $ids = array();

        foreach ($raw as $r) {
            $userId = $r['userId'];

            if (!isset($ids[$userId])) {
                $ids[$userId] = $r['androidId'];
            }
        }

        return array_values($ids);
    }
}