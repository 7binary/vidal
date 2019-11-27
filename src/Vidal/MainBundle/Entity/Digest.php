<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="DigestRepository")
 * @ORM\Table(name="digest")
 * @inheritdoc
 */
class Digest extends BaseEntity
{
    /** @ORM\Column(type="boolean") */
    protected $progress = false;

    /** @ORM\Column(type="text", nullable=true) */
    protected $text;

    /** @ORM\Column(type="text", nullable=true) */
    protected $footer;

    /** @ORM\Column(type="string", length=255) */
    protected $subject;

    /** @ORM\Column(type="string", length=500, nullable=true) */
    protected $emails;

    /**
     * @ORM\Column(type = "string", length = 255)
     * @Assert\Length(max = 255, maxMessage = "Идентификатор рассылки не может быть длиннее {{ limit }} символов")
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9_-]+$/",
     *     match=true,
     *     message="ТОЛЬКО латинские буквы, цифры и знаки _ -"
     * )
     * @var string
     */
    protected $uniqueid;

    /** @ORM\ManyToMany(targetEntity="Specialty", inversedBy="digests") */
    protected $specialties;

    /** @ORM\ManyToMany(targetEntity="Region", inversedBy="digests") */
    protected $regions;

    /** @ORM\Column(type="boolean") */
    protected $allSpecialties = true;

    /** @ORM\Column(type="string", length=255) */
    protected $font;

    /** @ORM\Column(type="integer") */
    protected $fontSize = 15;

    /** @ORM\Column(type="integer", nullable=true, name="limitBatch") */
    protected $limit;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $total;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $totalSend;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $totalLeft;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $totalLimit;

    /** @ORM\Column(type="text", nullable=true) */
    protected $excludedProducts;

    /** @ORM\Column(type="text", nullable=true) */
    protected $textPlain;

    public function __construct()
    {
        $this->specialties = new ArrayCollection();
        $this->regions = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param mixed $progress
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
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
     * Get subject
     *
     * @return string
     */
    public function getUniqueid()
    {
        return $this->uniqueid;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Digest
     */
    public function setUniqueid($uniqueid)
    {
        $this->uniqueid = $uniqueid;

        return $this;
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
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function limitMunus($step)
    {
        $limit = $this->limit - $step;
        $this->limit = $limit < 0 ? 0 : $limit;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getTotalSend()
    {
        return $this->totalSend;
    }

    /**
     * @param mixed $totalSend
     */
    public function setTotalSend($totalSend)
    {
        $this->totalSend = $totalSend;
    }

    /**
     * @return mixed
     */
    public function getTotalLeft()
    {
        return $this->totalLeft;
    }

    /**
     * @param mixed $totalLeft
     */
    public function setTotalLeft($totalLeft)
    {
        $this->totalLeft = $totalLeft < 0 ? 0 : $totalLeft;
    }

    public function updateTextImages()
    {
        $replaced = str_replace('src="/', 'src="http://www.vidal.ru/', $this->getText());
        $this->setText($replaced);
    }

    /**
     * @return mixed
     */
    public function getExcludedProducts()
    {
        return $this->excludedProducts;
    }

    /**
     * @param mixed $excludedProducts
     */
    public function setExcludedProducts($excludedProducts)
    {
        $this->excludedProducts = $excludedProducts;
    }

    /**
     * @return mixed
     */
    public function getTotalLimit()
    {
        return $this->totalLimit;
    }

    /**
     * @param mixed $totalLimit
     */
    public function setTotalLimit($totalLimit)
    {
        $this->totalLimit = $totalLimit;
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getFontSize()
    {
        return $this->fontSize;
    }

    /**
     * @param mixed $fontSize
     */
    public function setFontSize($fontSize)
    {
        $this->fontSize = $fontSize;
    }
}