<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SpecialtyRepository extends EntityRepository
{
	public function userSelectQb()
	{
        //  TODO option in database
    	$excludeName = "Не указано";

        $qb = $this->_em->createQueryBuilder();

        $qb->select('s')
            ->from('VidalMainBundle:Specialty', 's')
            ->andWhere('s.title <> :excludeName')
            ->setParameter('excludeName', $excludeName)
            ->orderBy('s.title', 'ASC');

        return $qb;
    }

	public function findByName($name)
	{
		return $this->_em->createQuery('
			SELECT s
			FROM VidalMainBundle:Specialty s
			WHERE s.title LIKE :name
			ORDER BY s.title ASC
		')->setParameter('name', $name)
			->setMaxResults(1)
			->getOneOrNullResult();
	}

    public function getBlankSpecialty()
    {
        return $this->_em->createQuery('
			SELECT s
			FROM VidalMainBundle:Specialty s
			WHERE s.id = :id
		')->setParameter('id', 110)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}