<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="productpicture") */
class ProductPicture
{
    /** @ORM\Id @ORM\Column(type="integer") */
    protected $ProductID;

    /** @ORM\Id @ORM\Column(type="integer") */
    protected $PictureID;

    /** @ORM\Column(length=20, nullable=true) */
    protected $productpicture;

    /** @ORM\Column(length=10, nullable=true) */
    protected $YearEdition;

    /** @ORM\Column(length=4, nullable=true) */
    protected $CountryEditionCode;

    /** @ORM\Column(length=4, nullable=true) */
    protected $EditionCode;

    /** @ORM\Column(length=10, nullable=true) */
    protected $DateEdit;

    /** @ORM\Column(length=20, nullable=true) */
    protected $DateEditFormatted;

    /** @ORM\Column(length=255, nullable=true) */
    protected $approvecode;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $IsForWeb;

    /** @ORM\Column(length=255, nullable=true) */
    protected $filename;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $found;

    /**
     * @param mixed $CountryEditionCode
     */
    public function setCountryEditionCode($CountryEditionCode)
    {
        $this->CountryEditionCode = $CountryEditionCode;
    }

    /**
     * @return mixed
     */
    public function getProductpicture()
    {
        return $this->productpicture;
    }

    /**
     * @param mixed $productpicture
     */
    public function setProductpicture($productpicture)
    {
        $this->productpicture = $productpicture;
    }

    /**
     * @return mixed
     */
    public function getDateEdit()
    {
        return $this->DateEdit;
    }

    /**
     * @param mixed $DateEdit
     */
    public function setDateEdit($DateEdit)
    {
        $this->DateEdit = $DateEdit;
    }

    /**
     * @return mixed
     */
    public function getIsForWeb()
    {
        return $this->IsForWeb;
    }

    /**
     * @param mixed $IsForWeb
     */
    public function setIsForWeb($IsForWeb)
    {
        $this->IsForWeb = $IsForWeb;
    }

    /**
     * @return mixed
     */
    public function getCountryEditionCode()
    {
        return $this->CountryEditionCode;
    }

    /**
     * @param mixed $EditionCode
     */
    public function setEditionCode($EditionCode)
    {
        $this->EditionCode = $EditionCode;
    }

    /**
     * @return mixed
     */
    public function getEditionCode()
    {
        return $this->EditionCode;
    }

    /**
     * @param mixed $PictureID
     */
    public function setPictureID($PictureID)
    {
        $this->PictureID = $PictureID;
    }

    /**
     * @return mixed
     */
    public function getPictureID()
    {
        return $this->PictureID;
    }

    /**
     * @param mixed $ProductID
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
    }

    /**
     * @return mixed
     */
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param mixed $YearEdition
     */
    public function setYearEdition($YearEdition)
    {
        $this->YearEdition = $YearEdition;
    }

    /**
     * @return mixed
     */
    public function getYearEdition()
    {
        return $this->YearEdition;
    }

    /**
     * @return mixed
     */
    public function getDateEditFormatted()
    {
        return $this->DateEditFormatted;
    }

    /**
     * @param mixed $DateEditFormatted
     */
    public function setDateEditFormatted($DateEditFormatted)
    {
        $this->DateEditFormatted = $DateEditFormatted;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getFound()
    {
        return $this->found;
    }

    /**
     * @param mixed $found
     */
    public function setFound($found)
    {
        $this->found = $found;
    }

    /**
     * @return mixed
     */
    public function getApprovecode()
    {
        return $this->approvecode;
    }

    /**
     * @param mixed $approvecode
     */
    public function setApprovecode($approvecode)
    {
        $this->approvecode = $approvecode;
    }

    public function getId()
    {
        return $this->ProductID . '+' . $this->PictureID;
    }
}