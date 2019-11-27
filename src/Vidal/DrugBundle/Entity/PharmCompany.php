<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="PharmCompanyRepository") @ORM\Table(name="pharm_company") */
class PharmCompany
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=255) */
	protected $title;

	/** @ORM\OneToMany(targetEntity="PharmArticle", mappedBy="company") */
	protected $articles;

	/** @ORM\ManyToMany(targetEntity="PharmArticle", mappedBy="companies") */
	protected $pharmArticles;

	/** @ORM\OneToMany(targetEntity="PharmPortfolio", mappedBy="company") */
	protected $portfolios;

	/** @ORM\Column(type="boolean") */
	protected $enabled;

	public function __construct()
	{
		$this->articles      = new ArrayCollection();
		$this->portfolios    = new ArrayCollection();
		$this->pharmArticles = new ArrayCollection();
		$this->enabled       = true;
	}

	public function __toString()
	{
		return $this->title;
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
	 * @param mixed $articles
	 */
	public function setArticles($articles)
	{
		$this->articles = $articles;
	}

	/**
	 * @return mixed
	 */
	public function getArticles()
	{
		return $this->articles;
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
	 * @return mixed
	 */
	public function getPharmArticles()
	{
		return $this->pharmArticles;
	}

	/**
	 * @param mixed $pharmArticles
	 */
	public function setPharmArticles($pharmArticles)
	{
		$this->pharmArticles = $pharmArticles;
	}
}