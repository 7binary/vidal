<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="LogRepository") @ORM\Table(name="log") */
class Log extends BaseEntity
{
    const EVENT_CREATE = 'create';
    const EVENT_DELETE = 'delete';
    const EVENT_UPDATE = 'update';

	/**
	 * @ORM\Column(length=50)
	 * @Assert\NotBlank(message="Укажите тип события")
	 */
	protected $event;

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank(message="Укажите класс сущности")
     */
    protected $entityClass;

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank(message="Укажите ID сущности")
     */
    protected $entityId;

	/**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Укажите ID администратора")
     */
	protected $adminId;

    /**
     * @ORM\Column(length=50)
     * @Assert\NotBlank(message="Укажите Email администратора")
     */
    protected $adminEmail;

    /** @ORM\Column(type="text", nullable=true) */
    protected $changes;

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * @param mixed $adminId
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;
    }

    /**
     * @return mixed
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * @param mixed $adminEmail
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }

    /**
     * @return mixed
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @param mixed $changes
     */
    public function setChanges($changes)
    {
        $this->changes = $changes;
    }
}