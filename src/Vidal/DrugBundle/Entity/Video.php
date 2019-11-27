<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="video") */
class Video extends BaseEntity
{
	/** @ORM\Column(length=255) */
	protected $path;

	/** @ORM\Column(type="integer") */
	protected $width;

	/** @ORM\Column(type="integer") */
	protected $height;

	/** @ORM\ManyToMany(targetEntity="PharmPortfolio", mappedBy="videos") */
	protected $portfolios;

	/** @ORM\ManyToMany(targetEntity="Art", mappedBy="videos") */
	protected $arts;

	/** @ORM\ManyToMany(targetEntity="Article", mappedBy="videos") */
	protected $articles;

	/** @ORM\ManyToMany(targetEntity="Publication", mappedBy="videos") */
	protected $publications;

	public function __toString()
	{
		return $this->path;
	}

	public function __construct()
	{
		$this->portfolios   = new ArrayCollection();
		$this->arts         = new ArrayCollection();
		$this->articles     = new ArrayCollection();
		$this->publications = new ArrayCollection();
	}

	/**
	 * @param mixed $path
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}

	/**
	 * @return mixed
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param mixed $portfolios
	 */
	public function setPortfolios($portfolios)
	{
		$this->portfolios = $portfolios;
	}

	/**
	 * @return mixed
	 */
	public function getPortfolios()
	{
		return $this->portfolios;
	}

	/**
	 * @param mixed $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return mixed
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param mixed $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @return mixed
	 */
	public function getHeight()
	{
		return $this->height;
	}
}