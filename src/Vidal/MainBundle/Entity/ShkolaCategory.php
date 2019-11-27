<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="ShkolaCategoryRepository") @ORM\Table(name="shkola_category") */
class ShkolaCategory
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=255) */
	protected $label;

	/** @ORM\Column(type = "boolean") */
	protected $enabled = true;

	/** @ORM\Column(length=255) */
	protected $url;

	/** @ORM\OneToMany(targetEntity="ShkolaArticle", mappedBy="category") */
	protected $articles;

	/** @ORM\Column(type="text", nullable=true) */
	protected $text;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/** @ORM\Column(length=255, nullable=true) */
	protected $title;

	/** @ORM\Column(length=255, nullable=true) */
	protected $description;

	/** @ORM\Column(length=255, nullable=true) */
	protected $keywords;

	/** @ORM\Column(type="text", nullable=true) */
	protected $about;

	public function __toString()
	{
		return empty($this->label) ? '' : $this->label;
	}

	public function __construct()
	{
		$this->articles = new ArrayCollection();
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
	 * @param mixed $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->text;
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
	 * @param mixed $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param mixed $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}

	/**
	 * @return mixed
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}

	/**
	 * @param mixed $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return mixed
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @return mixed
	 */
	public function getAbout()
	{
		return $this->about;
	}

	/**
	 * @param mixed $about
	 */
	public function setAbout($about)
	{
		$this->about = $about;
	}
}