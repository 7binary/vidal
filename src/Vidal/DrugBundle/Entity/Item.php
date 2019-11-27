<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="item") */
class Item
{
    /** @ORM\Id @ORM\Column(type="integer") */
    protected $ItemID;

    /** @ORM\Column(length=500) */
    protected $RusName;

    /** @ORM\Column(type="integer") */
    protected $FormID;

    /** @ORM\Column(type="text", nullable=true) */
    protected $DescriptionForm;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $PictureID;

    /**
     * @return mixed
     */
    public function getItemID()
    {
        return $this->ItemID;
    }

    /**
     * @param mixed $ItemID
     */
    public function setItemID($ItemID)
    {
        $this->ItemID = $ItemID;
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
    public function getDescriptionForm()
    {
        return $this->DescriptionForm;
    }

    /**
     * @param mixed $DescriptionForm
     */
    public function setDescriptionForm($DescriptionForm)
    {
        $this->DescriptionForm = $DescriptionForm;
    }

    /**
     * @return mixed
     */
    public function getPictureID()
    {
        return $this->PictureID;
    }

    /**
     * @param mixed $PictureID
     */
    public function setPictureID($PictureID)
    {
        $this->PictureID = $PictureID;
    }

    public function getId()
    {
        return $this->ItemID;
    }
}