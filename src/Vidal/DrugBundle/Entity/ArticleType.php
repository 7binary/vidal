<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="article_type") */
class ArticleType
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=255, unique=true) */
	protected $title;

	/** @ORM\OneToMany(targetEntity="Article", mappedBy="type") */
	protected $articles;

	public function __construct()
	{
		$this->articles = new ArrayCollection();
	}

	public function __toString()
	{
		return empty($this->title) ? '' : $this->title;
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
}