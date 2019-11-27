<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="form") */
class Form
{
    /** @ORM\Id @ORM\Column(type="integer") */
    protected $FormID;

    /** @ORM\Column(length=500, nullable=true) */
    protected $RusName;

    /** @ORM\Column(length=500, nullable=true) */
    protected $EngName;

    /** @ORM\Column(length=500, nullable=true) */
    protected $GDDB_FormID;

    /** @ORM\Column(length=500, nullable=true) */
    protected $ShortName;

    /**
     * @return mixed
     */
    public function getFormID()
    {
        return $this->FormID;
    }

    /**
     * @param mixed $FormID
     */
    public function setFormID($FormID)
    {
        $this->FormID = $FormID;
    }

    /**
     * @return mixed
     */
    public function getRusName()
    {
        return $this->RusName;
    }

    /**
     * @param mixed $RusName
     */
    public function setRusName($RusName)
    {
        $this->RusName = $RusName;
    }

    /**
     * @return mixed
     */
    public function getEngName()
    {
        return $this->EngName;
    }

    /**
     * @param mixed $EngName
     */
    public function setEngName($EngName)
    {
        $this->EngName = $EngName;
    }

    /**
     * @return mixed
     */
    public function getGDDBFormID()
    {
        return $this->GDDB_FormID;
    }

    /**
     * @param mixed $GDDB_FormID
     */
    public function setGDDBFormID($GDDB_FormID)
    {
        $this->GDDB_FormID = $GDDB_FormID;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->ShortName;
    }

    /**
     * @param mixed $ShortName
     */
    public function setShortName($ShortName)
    {
        $this->ShortName = $ShortName;
    }

    public function getId()
    {
        return $this->FormID;
    }
}