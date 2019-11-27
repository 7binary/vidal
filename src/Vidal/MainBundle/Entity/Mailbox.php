<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="MailboxRepository") @ORM\Table(name="mailbox", options={"charset"="utf8mb4","collate"="utf8mb4_unicode_ci"}) */
class Mailbox extends BaseEntity
{
	/** @ORM\Column(length=255, nullable=true) */
	protected $email;

    /** @ORM\Column(length=500, nullable=true) */
    protected $toString;

	/** @ORM\Column(length=500, nullable=true) */
	protected $subject;

	/** @ORM\Column(type="text", nullable=true) */
	protected $body;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $userId;

    /** @ORM\Column(length=255, nullable=true) */
    protected $statusCode;

    /** @ORM\Column(length=255, nullable=true) */
    protected $messageId;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $counter = 0;

    /** @ORM\Column(type="boolean") */
    protected $failed = false;

    /** @ORM\Column(length=255, nullable=true) */
    protected $error;

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
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
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
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param mixed $messageId
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * @return mixed
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * @param mixed $counter
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;
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
    public function getToString()
    {
        return $this->toString;
    }

    /**
     * @param mixed $toString
     */
    public function setToString($toString)
    {
        $this->toString = $toString;
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
}