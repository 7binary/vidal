<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="ArticleRubriqueRepository") @ORM\Table(name="article_rubrique") */
class ArticleRubrique
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=255, unique=true) */
	protected $title;

	/**
	 * @ORM\Column(length=255, nullable=true, unique=true)
	 * @Assert\Regex(
	 *     pattern="/[a-z\-]+/",
	 *     match=true,
	 *     message="Путь к рубрике может состоять только из латинских букв и тире"
	 * )
	 */
	protected $rubrique;

	/**
	 * @ORM\OneToMany(targetEntity="Article", mappedBy="rubrique")
	 */
	protected $articles;

	/** @ORM\Column(type="boolean") */
	protected $enabled = true;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/** @ORM\Column(length=255, nullable=true) */
	protected $redirect;

    /** @ORM\OneToMany(targetEntity="ArticleCategory", mappedBy="rubrique") */
    protected $categories;

    /** @ORM\Column(type="boolean") */
    protected $testMode = false;

	public function __construct()
	{
		$this->articles = new ArrayCollection();
        $this->categories = new ArrayCollection();
		$this->public   = true;
		$this->enabled  = true;
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
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
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

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
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
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param mixed $testMode
     */
    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
    }
}