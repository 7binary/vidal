<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="document_edition") */
class DocumentEdition
{
    /** @ORM\Id @ORM\Column(type="integer") */
    protected $DocumentID;

    /** @ORM\Id @ORM\Column(type="string") */
    protected $EditionCode;

    /** @ORM\Id @ORM\Column(type="string") */
    protected $Year;

    /** @ORM\Id @ORM\Column(type="string") */
    protected $CountryEditionCode;

    /**
     * @return mixed
     */
    public function getDocumentID()
    {
        return $this->DocumentID;
    }

    /**
     * @param mixed $DocumentID
     */
    public function setDocumentID($DocumentID)
    {
        $this->DocumentID = $DocumentID;
    }

    /**
     * @return mixed
     */
    public function getEditionCode()
    {
        return $this->EditionCode;
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
    public function getYear()
    {
        return $this->Year;
    }

    /**
     * @param mixed $Year
     */
    public function setYear($Year)
    {
        $this->Year = $Year;
    }

    /**
     * @return mixed
     */
    public function getCountryEditionCode()
    {
        return $this->CountryEditionCode;
    }

    /**
     * @param mixed $CountryEditionCode
     */
    public function setCountryEditionCode($CountryEditionCode)
    {
        $this->CountryEditionCode = $CountryEditionCode;
    }

    public function getId()
    {
        return $this->DocumentID . '+' . $this->EditionCode;
    }
}