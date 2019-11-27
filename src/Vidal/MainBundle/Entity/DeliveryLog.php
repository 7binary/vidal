<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="Vidal\MainBundle\Entity\DeliveryLogRepository") @ORM\Table(name="delivery_log") */
class DeliveryLog extends BaseEntity
{
    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $userId;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $uniqueid;

    /** @ORM\Column(type = "boolean") */
    protected $failed = false;

    /** @ORM\Column(type = "boolean") */
    protected $fake = false;

    /** @ORM\Column(type = "integer", nullable=true) */
    protected $send;

    /** @ORM\Column(length=500, nullable=true) */
    protected $error;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $messageId;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getUniqueid()
    {
        return $this->uniqueid;
    }

    /**
     * @param mixed $uniqueid
     */
    public function setUniqueid($uniqueid)
    {
        $this->uniqueid = $uniqueid;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * @param mixed $failed
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;
    }

    /**
     * @return mixed
     */
    public function getFake()
    {
        return $this->fake;
    }

    /**
     * @param mixed $fake
     */
    public function setFake($fake)
    {
        $this->fake = $fake;
    }

    /**
     * @return mixed
     */
    public function getSend()
    {
        return $this->send;
    }

    /**
     * @param mixed $send
     */
    public function setSend($send)
    {
        $this->send = $send;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    public function setMessageId()
    {
        $this->messageId = uniqid() . '@vidalru';
    }
}