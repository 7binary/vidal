<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="ArtRubriqueRepository") @ORM\Table(name="art_rubrique") */
class ArtRubrique
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(type="boolean") */
	protected $enabled;

	/** @ORM\Column(length=255) */
	protected $title;

	/** @ORM\Column(length=255) */
	protected $url;

	/** @ORM\Column(type="text", nullable=true) */
	protected $announce;

	/** @ORM\OneToMany(targetEntity="Art", mappedBy="rubrique") */
	protected $arts;

	/** @ORM\OneToMany(targetEntity="ArtType", mappedBy="rubrique", fetch="EXTRA_LAZY") */
	protected $types;

	/** @ORM\OneToMany(targetEntity="ArtCategory", mappedBy="rubrique") */
	protected $categories;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/** @ORM\Column(type="boolean") */
	protected $detached;

	/** @ORM\Column(length=255, nullable=true) */
	protected $redirect;

    /** @ORM\Column(type="text", nullable=true) */
    protected $seoTitle;

    /** @ORM\Column(type="text", nullable=true) */
    protected $seoDescription;

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

    public function __construct()
	{
		$this->enabled    = true;
		$this->detached   = false;
		$this->arts       = new ArrayCollection();
		$this->types      = new ArrayCollection();
		$this->categories = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->title;
	}

	public function getIs()
	{
		return 'rubrique';
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
	 * @param mixed $types
	 */
	public function setTypes($types)
	{
		$this->types = $types;
	}

	/**
	 * @return mixed
	 */
	public function getTypes()
	{
		return $this->types;
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
	 * @param mixed $detached
	 */
	public function setDetached($detached)
	{
		$this->detached = $detached;
	}

	/**
	 * @return mixed
	 */
	public function getDetached()
	{
		return $this->detached;
	}

	/**
	 * @param mixed $redirect
	 */
	public function setRedirect($redirect)
	{
		$this->redirect = $redirect;
	}

	/**
	 * @return mixed
	 */
	public function getRedirect()
	{
		return $this->redirect;
	}
}