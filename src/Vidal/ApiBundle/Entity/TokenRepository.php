<?php

namespace Vidal\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TokenRepository extends EntityRepository
{
    public function getToken($userName, $userPassword)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('token')
            ->from('VidalApiBundle:Token', 'token')
            ->where('token.userName = :userName')
            ->andWhere('token.userPassword = :userPassword')
            ->andWhere('token.enabled = true')
            ->setMaxResults(1)
            ->setParameter('userName', $userName)
            ->setParameter('userPassword', $userPassword);

        return $qb->getQuery()->getOneOrNullResult();
    }
}