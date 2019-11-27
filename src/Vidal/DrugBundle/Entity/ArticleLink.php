<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="article_link") */
class ArticleLink
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=255) */
	protected $title;

	/** @ORM\Column(length=255) */
	protected $url;

	/** @ORM\ManyToOne(targetEntity="Article", inversedBy="links") */
	protected $article;

	public function __toString()
	{
		return empty($this->title) ? '' : $this->title;
	}

	/**
	 * @param mixed $article
	 */
	public function setArticle($article)
	{
		$this->article = $article;
	}

	/**
	 * @return mixed
	 */
	public function getArticle()
	{
		return $this->article;
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
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
}