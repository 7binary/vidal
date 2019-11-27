<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/** @ORM\Entity(repositoryClass="TagRepository") @ORM\Table(name="tag") @UniqueEntity("text") */
class Tag
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=255, unique=true) */
	protected $text;

	/** @ORM\Column(length=255, nullable=true) */
	protected $search;

	/** @ORM\ManyToMany(targetEntity="Article", mappedBy="tags", fetch="EXTRA_LAZY") */
	protected $articles;

	/** @ORM\ManyToMany(targetEntity="Art", mappedBy="tags", fetch="EXTRA_LAZY") */
	protected $arts;

	/** @ORM\ManyToMany(targetEntity="Publication", mappedBy="tags", fetch="EXTRA_LAZY") */
	protected $publications;

	/** @ORM\ManyToMany(targetEntity="PharmArticle", mappedBy="tags", fetch="EXTRA_LAZY") */
	protected $pharmArticles;

	/**
	 * @ORM\OneToOne(targetEntity="InfoPage", mappedBy="tag")
	 * @ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")
	 */
	protected $infoPage;

	/** @ORM\Column(type="boolean") */
	protected $enabled = true;

	/**
	 * @ORM\OneToMany(targetEntity="TagHistory", mappedBy="tag")
	 * @ORM\OrderBy({"text" = "ASC"})
	 */
	protected $history;

	/** @ORM\Column(type="boolean") */
	protected $forCompany = false;

	/** @ORM\Column(type="integer") */
	protected $total = 0;

	public function __construct()
	{
		$this->articles      = new ArrayCollection();
		$this->arts          = new ArrayCollection();
		$this->publications  = new ArrayCollection();
		$this->pharmArticles = new ArrayCollection();
		$this->history       = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->text;
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
	 * @param mixed $text
	 */
	public function setText($text)
	{
		$this->text = trim($text);
	}

	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->text;
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
	 * @param mixed $pharmArticles
	 */
	public function setPharmArticles($pharmArticles)
	{
		$this->pharmArticles = $pharmArticles;
	}

	/**
	 * @return mixed
	 */
	public function getPharmArticles()
	{
		return $this->pharmArticles;
	}

	/**
	 * @param mixed $publications
	 */
	public function setPublications($publications)
	{
		$this->publications = $publications;
	}

	/**
	 * @return mixed
	 */
	public function getPublications()
	{
		return $this->publications;
	}

	/**
	 * @param mixed $search
	 */
	public function setSearch($search)
	{
		$this->search = trim($search);
	}

	/**
	 * @return mixed
	 */
	public function getSearch()
	{
		return $this->search;
	}

	/**
	 * @param mixed $infoPage
	 */
	public function setInfoPage($infoPage)
	{
		$this->infoPage = $infoPage;
	}

	/**
	 * @return mixed
	 */
	public function getInfoPage()
	{
		return $this->infoPage;
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
	 * @return mixed
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @param mixed $history
	 */
	public function setHistory($history)
	{
		$this->history = $history;
	}

	/**
	 * @return mixed
	 */
	public function getHistory()
	{
		return $this->history;
	}

	public function addTagHistory($history)
	{
		if (!$this->history->contains($history)) {
			$history->setTag($this);
			$this->history[] = $history;
		}
	}

	/**
	 * @param mixed $forCompany
	 */
	public function setForCompany($forCompany)
	{
		$this->forCompany = $forCompany;
	}

	/**
	 * @return mixed
	 */
	public function getForCompany()
	{
		return $this->forCompany;
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
	public function getTotal()
	{
		return $this->total;
	}

	public function countTotal()
	{
		$this->total++;
	}
}