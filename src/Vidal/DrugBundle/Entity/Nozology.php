<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="NozologyRepository") @ORM\Table(name="nozology") */
class Nozology
{
    /** @ORM\Id @ORM\Column(length=8) */
    protected $NozologyCode;

    /** @ORM\Column(length=8) */
    protected $Code;

    /**
     * @ORM\ManyToOne(targetEntity="Nozology", inversedBy="children")
     * @ORM\JoinColumn(name="ParentNozologyCode", referencedColumnName="NozologyCode")
     */
    protected $parent;

    /** @ORM\OneToMany(targetEntity="Nozology", mappedBy="parent") */
    protected $children;

    /** @ORM\Column(length=500, nullable=true) */
    protected $Name;

    /** @ORM\Column(length=3, nullable=true) */
    protected $Level;

    /**
     * @ORM\ManyToMany(targetEntity="Document", mappedBy="nozologies")
     * @ORM\JoinTable(name="document_indicnozology",
     *        joinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")})
     */
    protected $documents;

    /** @ORM\Column(length=20, nullable=true) */
    protected $Class;

    /**
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="nozologies")
     * @ORM\JoinTable(name="article_n",
     *        joinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $articles;

    /**
     * @ORM\ManyToMany(targetEntity="Art", mappedBy="nozologies")
     * @ORM\JoinTable(name="art_n",
     *        joinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="art_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $arts;

    /**
     * @ORM\ManyToMany(targetEntity="Publication", mappedBy="nozologies")
     * @ORM\JoinTable(name="publication_n",
     *        joinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $publications;

    /**
     * @ORM\ManyToMany(targetEntity="PharmArticle", mappedBy="nozologies")
     * @ORM\JoinTable(name="pharm_article_n",
     *        joinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")})
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $pharmArticles;

    /** @ORM\Column(type="integer") */
    protected $countProducts = 0;

    /** @ORM\Column(length=8, nullable=true) */
    protected $NozologyCode2;

    /**
     * @ORM\ManyToMany(targetEntity="Vidal\DrugBundle\Entity\Analitics", mappedBy="companies")
     * @ORM\JoinTable(name="analitics_nozologies",
     *        joinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="analitics_id", referencedColumnName="id")})
     */
    protected $analitics;

    public function __construct()
    {
        $this->analitics = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->arts = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->pharmArticles = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->NozologyCode . ' - ' . $this->Name;
    }

    /**
     * @return mixed
     */
    public function getAnalitics()
    {
        return $this->analitics;
    }

    /**
     * @param mixed $analitics
     */
    public function setAnalitics($analitics)
    {
        $this->analitics = $analitics;
    }

    /**
     * @param mixed $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param mixed $NozologyCode
     */
    public function setNozologyCode($NozologyCode)
    {
        $this->NozologyCode = $NozologyCode;
    }

    /**
     * @return mixed
     */
    public function getNozologyCode()
    {
        return $this->NozologyCode;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $Level
     */
    public function setLevel($Level)
    {
        $this->Level = $Level;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->Level;
    }

    /**
     * @param mixed $documents
     */
    public function setDocuments(ArrayCollection $documents)
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
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->Class = $class;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->Class;
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
     * @param mixed $countProducts
     */
    public function setCountProducts($countProducts)
    {
        $this->countProducts = $countProducts;
    }

    /**
     * @return mixed
     */
    public function getCountProducts()
    {
        return $this->countProducts;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getNozologyCode2()
    {
        return $this->NozologyCode2;
    }

    /**
     * @param mixed $NozologyCode2
     */
    public function setNozologyCode2($NozologyCode2)
    {
        $this->NozologyCode2 = $NozologyCode2;
    }

    public function getId()
    {
        return $this->NozologyCode;
    }
}