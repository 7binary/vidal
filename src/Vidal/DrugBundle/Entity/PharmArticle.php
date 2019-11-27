<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="PharmArticleRepository") @ORM\Table(name="pharm_article") */
class PharmArticle extends BaseEntity
{
	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/** @ORM\Column(type="text") */
	protected $text;

	/** @ORM\ManyToOne(targetEntity="PharmCompany", inversedBy="articles") */
	protected $company;

	/** @ORM\ManyToMany(targetEntity="PharmCompany", inversedBy="pharmArticles") */
	protected $companies;

	/** @ORM\Column(length=10, nullable=true) */
	protected $hidden;

	/**
	 * @ORM\ManyToMany(targetEntity="Nozology", inversedBy="pharmArticles")
	 * @ORM\JoinTable(name="pharm_article_n",
	 *        joinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")})
	 */
	protected $nozologies;

	/**
	 * @ORM\ManyToMany(targetEntity="Molecule", inversedBy="pharmArticles")
	 * @ORM\JoinTable(name="pharm_article_molecule",
	 *        joinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="MoleculeID", referencedColumnName="MoleculeID")})
	 */
	protected $molecules;

	/**
	 * @ORM\ManyToMany(targetEntity="Document", inversedBy="pharmArticles")
	 * @ORM\JoinTable(name="pharm_article_document",
	 *        joinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")})
	 */
	protected $documents;

	/**
	 * @ORM\ManyToMany(targetEntity="Product", inversedBy="pharmArticles")
	 * @ORM\JoinTable(name="pharm_article_product",
	 *        joinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")})
	 */
	protected $products;

	/**
	 * @ORM\ManyToMany(targetEntity="ATC", inversedBy="pharmArticles")
	 * @ORM\JoinTable(name="pharm_article_atc",
	 *        joinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="ATCCode", referencedColumnName="ATCCode")})
	 */
	protected $atcCodes;

	/**
	 * @ORM\ManyToMany(targetEntity="InfoPage", inversedBy="pharmArticles")
	 * @ORM\JoinTable(name="pharm_article_infopage",
	 *        joinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")})
	 */
	protected $infoPages;

	/** @ORM\ManyToMany(targetEntity="Tag", inversedBy="pharmArticles") */
	protected $tags;

	public function __construct()
	{
		$this->nozologies = new ArrayCollection();
		$this->molecules  = new ArrayCollection();
		$this->documents  = new ArrayCollection();
		$this->products   = new ArrayCollection();
		$this->atcCodes   = new ArrayCollection();
		$this->infoPages  = new ArrayCollection();
		$this->tags       = new ArrayCollection();
		$this->companies  = new ArrayCollection();
		$now              = new \DateTime('now');
		$this->created    = $now;
		$this->updated    = $now;
	}

	public function __toString()
	{
		return $this->id . '';
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
	 * @param mixed $company
	 */
	public function setCompany($company)
	{
		$this->company = $company;
	}

	/**
	 * @return mixed
	 */
	public function getCompany()
	{
		return $this->company;
	}

	/**
	 * @param mixed $atcCodes
	 */
	public function setAtcCodes($atcCodes)
	{
		$this->atcCodes = $atcCodes;
	}

	/**
	 * @return mixed
	 */
	public function getAtcCodes()
	{
		return $this->atcCodes;
	}

	/**
	 * @param mixed $documents
	 */
	public function setDocuments($documents)
	{
		$this->documents = $documents;
	}

	/**
	 * @return mixed
	 */
	public function getDocuments()
	{
		return $this->documents;
	}

	/**
	 * @param mixed $hidden
	 */
	public function setHidden($hidden)
	{
		$this->hidden = $hidden;
	}

	/**
	 * @return mixed
	 */
	public function getHidden()
	{
		return $this->hidden;
	}

	/**
	 * @param mixed $infoPages
	 */
	public function setInfoPages($infoPages)
	{
		$this->infoPages = $infoPages;
	}

	/**
	 * @return mixed
	 */
	public function getInfoPages()
	{
		return $this->infoPages;
	}

	/**
	 * @param mixed $molecules
	 */
	public function setMolecules($molecules)
	{
		$this->molecules = $molecules;
	}

	/**
	 * @return mixed
	 */
	public function getMolecules()
	{
		return $this->molecules;
	}

	/**
	 * @param mixed $nozologies
	 */
	public function setNozologies($nozologies)
	{
		$this->nozologies = $nozologies;
	}

	/**
	 * @return mixed
	 */
	public function getNozologies()
	{
		return $this->nozologies;
	}

	/**
	 * @param mixed $tags
	 */
	public function setTags($tags)
	{
		$this->tags = $tags;
	}

	/**
	 * @return mixed
	 */
	public function getTags()
	{
		return $this->tags;
	}

	public function addTag(Tag $tag)
	{
		if (!$this->tags->contains($tag)) {
			$this->tags[] = $tag;
		}

		return $this;
	}

	public function removeTag($tag)
	{
		$this->tags->removeElement($tag);
	}

	public function addDocument(Document $document)
	{
		if (!$this->documents->contains($document)) {
			$this->documents[] = $document;
		}

		return $this;
	}

	public function removeDocument(Document $document)
	{
		$this->documents->removeElement($document);
	}

	public function getT()
	{
		return 'PharmArticle';
	}

	/**
	 * @param mixed $products
	 */
	public function setProducts($products)
	{
		$this->products = $products;
	}

	/**
	 * @return mixed
	 */
	public function getProducts()
	{
		return $this->products;
	}

	public function addProduct(Product $product)
	{
		if (!$this->products->contains($product)) {
			$this->products[] = $product;
		}
	}

	public function removeProduct(Product $product)
	{
		$this->products->removeElement($product);
	}

	public function addAtcCode($atcCode)
	{
		$this->atcCodes[] = $atcCode;

		return $this;
	}

	public function removeAtcCode($atcCode)
	{
		$this->atcCodes->removeElement($atcCode);
	}

	public function addNozology($nozology)
	{
		$this->nozologies[] = $nozology;

		return $this;
	}

	public function removeNozology($nozology)
	{
		$this->nozologies->removeElement($nozology);

		return $this;
	}

	public function addMolecule($molecule)
	{
		$this->molecules[] = $molecule;

		return $this;
	}

	public function removeMolecule($molecule)
	{
		$this->molecules->removeElement($molecule);

		return $this;
	}

	public function addInfoPage($infoPage)
	{
		$this->infoPages[] = $infoPage;

		return $this;
	}

	public function removeInfoPage($infoPage)
	{
		$this->infoPages->removeElement($infoPage);

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCompanies()
	{
		return $this->companies;
	}

	/**
	 * @param mixed $companies
	 */
	public function setCompanies($companies)
	{
		$this->companies = $companies;
	}

	public function addCompany($c)
	{
		$this->companies[] = $c;

		return $this;
	}

	public function removeCompany($c)
	{
		$this->companies->removeElement($c);
	}
}