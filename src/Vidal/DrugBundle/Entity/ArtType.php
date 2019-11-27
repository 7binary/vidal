<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="ArtTypeRepository") @ORM\Table(name="art_type") */
class ArtType
{
    /** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
    protected $id;

    /** @ORM\Column(length=255) */
    protected $title;

    /** @ORM\Column(type="text", nullable=true) */
    protected $announce;

    /** @ORM\Column(type="text", nullable=true) */
    protected $seoTitle;

    /** @ORM\Column(type="text", nullable=true) */
    protected $seoDescription;

    /** @ORM\Column(length=255) */
    protected $url;

    /** @ORM\Column(type="boolean") */
    protected $enabled;

    /** @ORM\OneToMany(targetEntity="Art", mappedBy="type") */
    protected $arts;

    /** @ORM\ManyToOne(targetEntity="ArtRubrique", inversedBy="types") */
    protected $rubrique;

    /** @ORM\OneToMany(targetEntity="ArtCategory", mappedBy="type", fetch="EXTRA_LAZY") */
    protected $categories;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $priority;

    public function __construct()
    {
        $this->enabled = true;
        $this->arts = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function __toString()
    {
        return empty($this->title) ? '' : $this->title;
    }

    public function getIs()
    {
        return 'type';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $arts
     */
    public function setArts($arts)
    {
        $this->arts = $arts;
    }

    /**
     * @return mixed
     */
    public function getArts()
    {
        return $this->arts;
    }

    /**
     * @param mixed $rubrique
     */
    public function setRubrique($rubrique)
    {
        $this->rubrique = $rubrique;
    }

    /**
     * @return mixed
     */
    public function getRubrique()
    {
        return $this->rubrique;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $announce
     */
    public function setAnnounce($announce)
    {
        $this->announce = $announce;
    }

    /**
     * @return mixed
     */
    public function getAnnounce()
    {
        return $this->announce;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return mixed
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param mixed $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return mixed
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @param mixed $seoDescription
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }
}