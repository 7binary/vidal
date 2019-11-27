<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="Vidal\MainBundle\Entity\DeliveryRepository") @ORM\Table(name="delivery") */
class Delivery extends BaseEntity
{
    /**
     * @ORM\Column(type = "string", length = 255)
     * @Assert\NotBlank()
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type = "string", length = 255, nullable=true)
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type = "integer")
     * @Assert\NotBlank()
     * @var integer
     */
    protected $coef = 100;

    /**
     * @ORM\Column(type = "integer")
     * @Assert\NotBlank()
     * @var float
     */
    protected $coefSent = 100;

    /** @ORM\Column(type="text", nullable=true) */
    protected $text;

    /** @ORM\Column(type="text", nullable=true) */
    protected $footer;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $subject;

    /** @ORM\Column(type="string", length=500, nullable=true) */
    protected $emails;

    /** @ORM\Column(type="string", length=500, nullable=true) */
    protected $specialties;

    /** @ORM\Column(type="string", length=500, nullable=true) */
    protected $regions;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $allSpecialties = false;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $font;

    /** @ORM\Column(type="text", nullable=true) */
    protected $textPlain;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUniqueid()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getCoef()
    {
        return $this->coef / 100;
    }

    /**
     * @param float $coef
     */
    public function setCoef($coef)
    {
        $this->coef = intval(floatval(str_replace(',','.', trim($coef))) * 100);
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param mixed $footer
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
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
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param mixed $emails
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }

    /**
     * @return mixed
     */
    public function getSpecialties()
    {
        return $this->specialties;
    }

    /**
     * @param mixed $specialties
     */
    public function setSpecialties($specialties)
    {
        $this->specialties = $specialties;
    }

    /**
     * @return mixed
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * @param mixed $regions
     */
    public function setRegions($regions)
    {
        $this->regions = $regions;
    }

    /**
     * @return mixed
     */
    public function getAllSpecialties()
    {
        return $this->allSpecialties;
    }

    /**
     * @param mixed $allSpecialties
     */
    public function setAllSpecialties($allSpecialties)
    {
        $this->allSpecialties = $allSpecialties;
    }

    /**
     * @return mixed
     */
    public function getFont()
    {
        return $this->font;
    }

    /**
     * @param mixed $font
     */
    public function setFont($font)
    {
        $this->font = $font;
    }

    /**
     * @return mixed
     */
    public function getTextPlain()
    {
        return $this->textPlain;
    }

    /**
     * @param mixed $textPlain
     */
    public function setTextPlain($textPlain)
    {
        $this->textPlain = $textPlain;
    }

    /**
     * @return float
     */
    public function getCoefSent()
    {
        return $this->coefSent ? ($this->coefSent  / 100) : 1;
    }

    /**
     * @param float $coef
     */
    public function setCoefSent($coefSent)
    {
        $this->coefSent = intval(floatval(str_replace(',','.', trim($coefSent))) * 100);
    }
}