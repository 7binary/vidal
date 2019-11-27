<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity() @ORM\Table(name="appointment") */
class Appointment
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * Номер полиса ПМС
     * @ORM\Column(type="string")
     */
    protected $OMSCode;

    /**
     * @ORM\Column(type="date")
     */
    protected $birthdate;

    /**
     * Медицинское учреждение
     * @ORM\Column(type="string", nullable=true)
     */
    protected $healthFacility;

    /**
     * Специальность доктора
     * @ORM\Column(type="string", nullable=true)
     */
    protected $doctorSpecialty;

    /**
     * ФИО доктора
     * @ORM\Column(type="string", nullable=true)
     */
    protected $doctorFio;

    /**
     * Статус (1 - Действительный, 0-отмененный)
     * @ORM\Column(type="boolean")
     */
    protected $status = 1;

    /**
     * Номер записи
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderNumber;

    /**
     * Дата и время начала
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $starts;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * Дата и время начала
     * @ORM\Column(type="datetime")
     */
    protected $updated;


    public function __construct(){
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * @param mixed $OMSCode
     */
    public function setOMSCode($OMSCode)
    {
        $this->OMSCode = $OMSCode;
    }

    /**
     * @return mixed
     */
    public function getOMSCode()
    {
        return $this->OMSCode;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $doctorFio
     */
    public function setDoctorFio($doctorFio)
    {
        $this->doctorFio = $doctorFio;
    }

    /**
     * @return mixed
     */
    public function getDoctorFio()
    {
        return $this->doctorFio;
    }

    /**
     * @param mixed $doctorSpecialty
     */
    public function setDoctorSpecialty($doctorSpecialty)
    {
        $this->doctorSpecialty = $doctorSpecialty;
    }

    /**
     * @return mixed
     */
    public function getDoctorSpecialty()
    {
        return $this->doctorSpecialty;
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $healthFacility
     */
    public function setHealthFacility($healthFacility)
    {
        $this->healthFacility = $healthFacility;
    }

    /**
     * @return mixed
     */
    public function getHealthFacility()
    {
        return $this->healthFacility;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param mixed $starts
     */
    public function setStarts($starts)
    {
        $this->starts = $starts;
    }

    /**
     * @return mixed
     */
    public function getStarts()
    {
        return $this->starts;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status = 1)
    {
        $this->status = $status;
        $this->updated = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $birthdate
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    /**
     * @return mixed
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }
}