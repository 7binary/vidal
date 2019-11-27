<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UploadUsersRepository extends EntityRepository
{
    public function findAll()
    {
        return $this->findBy(array(), array('created' => 'DESC'));
    }

    public function findLast()
    {
        return $this->findOneBy(array(), array('id' => 'DESC'));
    }

    public function findToLoad()
    {
        return $this->findBy(array('status' => UploadUsers::STATUS_NEW), array('created' => 'ASC'));
    }

    public function findToProcess()
    {
        return $this->findBy(array('status' => UploadUsers::STATUS_LOADED), array('created' => 'ASC'));
    }
}