<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="ShkolaArticleRepository") @ORM\Table(name="shkola_article") @Filestore\Uploadable */
class ShkolaArticle extends BaseEntity
{
	/** @ORM\Column(length=255) */
	protected $label;

	/** @ORM\Column(type="text") */
	protected $text;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @Filestore\UploadableField(mapping="blog")
	 */
	protected $photo;

	/** @ORM\ManyToOne(targetEntity="ShkolaCategory", inversedBy="articles") */
	protected $category;

	/** @ORM\Column(length=255) */
	protected $url;

	/** @ORM\Column(length=255, nullable=true) */
	protected $title;

	/** @ORM\Column(length=255, nullable=true) */
	protected $keywords;

	/** @ORM\Column(length=255, nullable=true) */
	protected $description;

	/** @ORM\Column(type="boolean") */
	protected $categoryPage = false;

	public function __toString()
	{
		return empty($this->label) ? '' : $this->label;
	}

	/**
	 * @param mixed $photo
	 */
	public function setPhoto($photo)
	{
		$this->photo = $photo;
	}

	/**
	 * @return mixed
	 */
	public function getPhoto()
	{
		return $this->photo;
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
	 * @param mixed $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
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
	public function getCategoryPage()
	{
		return $this->categoryPage;
	}

	/**
	 * @param mixed $categoryPage
	 */
	public function setCategoryPage($categoryPage)
	{
		$this->categoryPage = $categoryPage;
	}
}