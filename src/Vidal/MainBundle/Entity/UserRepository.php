<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function setDeliveryLogFailed(User $user, $days = 2)
    {
        $daysAgo = new \DateTime();
        $daysAgo->modify("-$days days");
        $daysAgo = $daysAgo->format('Y-m-d H:i:s');

        /** @var DeliveryLog[] $logs */
        $logs = $this->_em->createQuery("
            SELECT l
            FROM VidalMainBundle:DeliveryLog l
            WHERE l.userId = :userId
              AND l.created > '$daysAgo'
        ")->setParameter('userId', $user->getId())
            ->getResult();

        if (empty($logs)) {
            $log = $this->_em->createQuery("
                SELECT l
                FROM VidalMainBundle:DeliveryLog l
                WHERE l.userId = :userId
                ORDER BY l.created DESC
            ")->setMaxResults(1)
                ->setParameter('userId', $user->getId())
                ->getOneOrNullResult();

            if ($log) {
                $log->setFailed(true);
                $this->_em->flush($log);
            }
        }
        else {
            foreach ($logs as $log) {
                $log->setFailed(true);
                $this->_em->flush($log);
            }
        }
    }

    /**
     * @param string $login
     * @return User
     */
    public function findOneByLogin($login)
    {
        return $this->_em->createQuery('
			SELECT u
			FROM VidalMainBundle:User u
			WHERE u.username = :login
				OR u.oldLogin = :login
		')->setParameter('login', $login)
            ->getOneOrNullResult();
    }

    public function findDeviceByAndroidId($androidId)
    {
        return $this->_em->createQuery("
		 	SELECT u
		 	FROM VidalMainBundle:UserDevice u
		 	WHERE u.androidId = :androidId
		")->setParameter('androidId', $this->escape_like($androidId))
            ->getOneOrNullResult();
    }

    private function escape_like($string)
    {
        $search = array('%', '_');
        $replace = array('\%', '\_');
        return str_replace($search, $replace, $string);
    }

    public function findDevicesWithoutGCM()
    {
        return $this->_em->createQuery("
		 	SELECT u
		 	FROM VidalMainBundle:UserDevice u
		 	WHERE u.androidId IS NOT NULL
		 	  AND u.androidId != ''
		 	  AND u.gcm IS NULL
		 	ORDER BY u.id ASC
		")->getResult();
    }

    public function resetAutoregister()
    {
        $this->_em->createQuery("UPDATE VidalMainBundle:User u SET u.autoregister = FALSE")->execute();
    }

    public function findDevices()
    {
        $raw = $this->_em->createQuery("
		 	SELECT u.androidId, u.id
		 	FROM VidalMainBundle:UserDevice u
		 	WHERE u.androidId IS NOT NULL AND u.androidId != ''
		 	ORDER BY u.id ASC
		")->getResult();

        $users = array();

        foreach ($raw as $r) {
            $users[] = $r['androidId'];
        }

        return array_unique($users);
    }

    public function testDevicesGrouped()
    {
        $ids = array(
            'eGFy05Q0V84:APA91bHok_Glm7NbRc4ZftWJsRwlpe5LLuGy09xD0nC4_XskPU_lk2wJS2IHei87h88aAA59EO8immRFPgnwRbxIvc26KmD29pHZy05czDoRjJYlXfsWnKKKB_rPs9vGV1up2iVyZDVP',
            'fHhkNhF0Y3k:APA91bGeOObedy218fkAk5N342b6rxOxxUhzBRi7jgaRea4ZvgCX5vdfOfYtzYIHGZHN7Z80kAje_x4_eiC4u4JL_eEG3-q7Zzy69xAFXzTRGCxPyS2EvcJ5ERQn4PbQTh8vtS_LxiIz',
            'dulOyH4wqDg:APA91bHQsKNTrNNtpUVe3Sbg7K73mvXlUuDjC2lXhido8EdBl76SfJvhP0ueffla1u-7X7vTimiTS-NFhQqjPF8aBRc8hZsHuhWkZhBBc7jvJcqElZA2wHL5MbIATH0C_tqpBs6eW7Zg',
            'fBcPbkWrW-E:APA91bF3zlZen5AyTkarc4QI-dKRSQCsMJ658MXr5rYMcliolGAB-tFM7HbzT4WuRfifgJoAXwkZzgSf3snBoSfcEoqiRCB-GPsEXMKLaM0PGh9mffU441rUKGbXQ6oBvmucV-OTSrlF',
        );

        $raw = $this->_em->createQuery("
		 	SELECT DISTINCT u.androidId, u.id, u.gcm
		 	FROM VidalMainBundle:UserDevice u
		 	WHERE u.androidId IN (:ids)
		 	ORDER BY u.id ASC
		")->setParameter('ids', $ids)
            ->getResult();

        $data = array();

        foreach ($raw as $r) {
            $key = $r['gcm'];
            if (isset($data[$key])) {
                $data[$key][] = $r['androidId'];
            }
            else {
                $data[$key] = array($r['androidId']);
            }
        }

        return $data;
    }

    public function findDevicesGrouped()
    {
        $raw = $this->_em->createQuery("
		 	SELECT DISTINCT u.androidId, u.id, u.gcm
		 	FROM VidalMainBundle:UserDevice u
		 	WHERE u.androidId IS NOT NULL
		 	  AND u.androidId != ''
		 	  AND u.gcm IS NOT NULL
		 	  AND u.gcm != ''
		 	ORDER BY u.id ASC
		")->getResult();

        $data = array();

        foreach ($raw as $r) {
            $key = $r['gcm'];
            if (isset($data[$key])) {
                $data[$key][] = $r['androidId'];
            }
            else {
                $data[$key] = array($r['androidId']);
            }
        }

        return $data;
    }

    public function findDevicesGroupedByProject($project)
    {
        $raw = $this->_em->createQuery("
		 	SELECT DISTINCT u.androidId, u.id, u.gcm
		 	FROM VidalMainBundle:UserDevice u
		 	WHERE u.androidId IS NOT NULL
		 	  AND u.androidId != ''
		 	  AND u.gcm IS NOT NULL
		 	  AND u.gcm != ''
		 	  AND u.project = '$project'
		 	ORDER BY u.id ASC
		")->getResult();

        $data = array();

        foreach ($raw as $r) {
            $key = $r['gcm'];
            if (isset($data[$key])) {
                $data[$key][] = $r['androidId'];
            }
            else {
                $data[$key] = array($r['androidId']);
            }
        }

        return $data;
    }

    public function findProfile($username)
    {
        $users = $this->_em->createQuery('
		 	SELECT u.username, u.lastName, u.firstName, u.surName,
		 		s.title as specialization, ps.title as primarySpecialty, ss.title as secondarySpecialty,
		 		c.title as city, re.title as region, co.title as country,
		 		uni.title as university, u.school,
		 		u.graduateYear, u.birthdate, u.academicDegree, u.phone, u.icq, u.educationType,
		 		u.dissertation, u.professionalInterests, u.jobPlace, u.jobSite, u.jobPosition, u.jobStage,
		 		u.jobAchievements, u.jobPublications, u.about, u.oldUser, u.created
		 	FROM VidalMainBundle:User u
		 	LEFT JOIN u.specialization s
		 	LEFT JOIN u.primarySpecialty ps
		 	LEFT JOIN u.secondarySpecialty ss
		 	LEFT JOIN u.city c
		 	LEFT JOIN u.university uni
		 	LEFT JOIN u.region re
		 	LEFT JOIN u.country co
		 	WHERE u.username = :username
		 	ORDER BY u.id ASC
		')
            ->setParameter('username', $username)
            ->getOneOrNullResult();

        return $users;
    }

    public function findUsersExcel()
    {
        $users = $this->_em->createQuery('
		 	SELECT u.username, u.lastName, u.firstName, u.surName,
		 		s.title as specialization, ps.title as primarySpecialty, ss.title as secondarySpecialty,
		 		c.title as city, re.title as region, co.title as country,
		 		uni.title as university, u.school,
		 		u.graduateYear, u.birthdate, u.academicDegree, u.phone, u.icq, u.educationType,
		 		u.dissertation, u.professionalInterests, u.jobPlace, u.jobSite, u.jobPosition, u.jobStage,
		 		u.jobAchievements, u.jobPublications, u.about, u.oldUser, u.created
		 	FROM VidalMainBundle:User u
		 	LEFT JOIN u.specialization s
		 	LEFT JOIN u.primarySpecialty ps
		 	LEFT JOIN u.secondarySpecialty ss
		 	LEFT JOIN u.city c
		 	LEFT JOIN u.university uni
		 	LEFT JOIN u.region re
		 	LEFT JOIN u.country co
		 	ORDER BY u.id ASC
		')
            ->getResult();

        return $users;
    }

    public function forExcel($number = null, $onlySubs = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("u.id, s1.title as specialty1, s2.title as specialty2, c.title as city, r.title as region, co.title as country, DATE_FORMAT(u.created, '%Y-%m-%d') as registered, u.lastName, u.firstName, u.surName, u.digestSubscribed")
            ->from('VidalMainBundle:User', 'u')
            ->leftJoin('u.city', 'c')
            ->leftJoin('c.region', 'r')
            ->leftJoin('c.country', 'co')
            ->leftJoin('u.primarySpecialty', 's1')
            ->leftJoin('u.secondarySpecialty', 's2')
            ->orderBy('u.username', 'ASC');

        if ($number > 2000) {
            $created = new \DateTime("$number-01-01 00:00:00");
            $qb->where('u.created > :created')->setParameter('created', $created);
        }
        elseif ($number > 0 && $number <= 12) {
            $year = date('Y');
            $month = date('m');
            if ($number > $month) {
                $year--;
            }
            $created = new \DateTime("$year-$number-01 00:00:00");
            $nextMonth = new \DateTime("$year-$number-01 00:00:00");
            $nextMonth->modify('+1 month');
            $qb->where('u.created > :created')
                ->andWhere('u.created < :nextMonth')
                ->setParameter('created', $created)
                ->setParameter('nextMonth', $nextMonth);
        }

        if ($onlySubs) {
            $qb->andWhere('u.digestSubscribed = TRUE');
        }

        return $qb->getQuery()->getResult();
    }

    public function checkOldPassword($password, $pwReal)
    {
        $pdo = $this->_em->getConnection();

        $stmt = $pdo->prepare("SELECT PASSWORD('$password') as password");
        $stmt->execute();
        $pw1 = $stmt->fetch();
        $pw1 = $pw1['password'];

        $stmt = $pdo->prepare("SELECT OLD_PASSWORD('$password') as password");
        $stmt->execute();
        $pw2 = $stmt->fetch();
        $pw2 = $pw2['password'];

        return $pw1 === $pwReal || $pw2 === $pwReal;
    }

    public function total()
    {
        return $this->_em->createQuery('
			SELECT COUNT(u.id)
			FROM VidalMainBundle:User u
		')->getSingleScalarResult();
    }
}