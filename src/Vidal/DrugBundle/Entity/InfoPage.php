<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="InfoPageRepository") @ORM\Table(name="infopage") @FileStore\Uploadable */
class InfoPage
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    protected $InfoPageID;

    /** @ORM\Column(length=255) */
    protected $RusName;

    /** @ORM\Column(length=255, nullable=true) */
    protected $EngName;

    /** @ORM\Column(type="text", nullable=true) */
    protected $RusAddress;

    /** @ORM\Column(type="text", nullable=true) */
    protected $EngAddress;

    /** @ORM\Column(type="text", nullable=true) */
    protected $ShortAddress;

    /** @ORM\Column(length=255, nullable=true) */
    protected $approvecode;

    /**
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="infoPages")
     * @ORM\JoinColumn(name="CountryCode", referencedColumnName="CountryCode")
     */
    protected $CountryCode;

    /** @ORM\Column(type="text", nullable=true) */
    protected $Notes;

    /** @ORM\Column(length=100, nullable=true) */
    protected $PhoneNumber;

    /** @ORM\Column(length=100, nullable=true) */
    protected $Fax;

    /** @ORM\Column(length=100, nullable=true) */
    protected $Email;

    /** @ORM\Column(type="boolean") */
    protected $WithoutPage = false;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $DateTextModified;

    /**
     * @ORM\Column(length=10)
     */
    protected $CountryEditionCode = 'RUS';

    /** @ORM\ManyToMany(targetEntity="Picture", mappedBy="infoPages") */
    protected $pictures;

    /**
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="infoPages", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="article_infopage",
     *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $articles;

    /**
     * @ORM\ManyToMany(targetEntity="Art", mappedBy="infoPages", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="art_infopage",
     *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="art_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $arts;

    /**
     * @ORM\ManyToMany(targetEntity="Publication", mappedBy="infoPages", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="publication_infopage",
     *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $publications;

    /**
     * @ORM\ManyToMany(targetEntity="PharmArticle", mappedBy="infoPages")
     * @ORM\JoinTable(name="pharm_article_infopage",
     *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")})
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $pharmArticles;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $countProducts = 1;

    /**
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="infoPages")
     * @ORM\JoinTable(name="product_infopage",
     *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")})
     */
    protected $products;

    /**
     * @ORM\ManyToMany(targetEntity="Document", mappedBy="infoPages")
     * @ORM\JoinTable(name="document_info_page",
     *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")})
     */
    protected $documents;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="infopage_photo")
     */
    protected $photo;

    /**
     * @ORM\OneToOne(targetEntity="Tag", inversedBy="infoPage")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     */
    protected $tag;

    /** @ORM\Column(type="string", nullable=true) */
    protected $logo;

    /** @ORM\Column(type="string", nullable=true) */
    protected $Url;

    /**
     * @ORM\ManyToMany(targetEntity="Vidal\DrugBundle\Entity\Analitics", mappedBy="infoPages")
     * @ORM\JoinTable(name="analitics_infopages",
     *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="analitics_id", referencedColumnName="id")})
     */
    protected $analitics;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->arts = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->pharmArticles = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->analitics = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->InfoPageID . ' - ' . $this->RusName;
    }

    /**
     * @param mixed $DateTextModified
     */

    public function setDateTextModified($DateTextModified)
    {
        $this->DateTextModified = $DateTextModified;
    }

    /**
     * @return mixed
     */
    public function getDateTextModified()
    {
        return $this->DateTextModified;
    }

    /**
     * @param mixed $Email
     */
    public function setEmail($Email)
    {
        $this->Email = $Email;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * @param mixed $EngAddress
     */
    public function setEngAddress($EngAddress)
    {
        $this->EngAddress = $EngAddress;
    }

    /**
     * @return mixed
     */
    public function getEngAddress()
    {
        return $this->EngAddress;
    }

    /**
     * @param mixed $EngName
     */
    public function setEngName($EngName)
    {
        $this->EngName = $EngName;
    }

    /**
     * @return mixed
     */
    public function getEngName()
    {
        return $this->EngName;
    }

    /**
     * @param mixed $Fax
     */
    public function setFax($Fax)
    {
        $this->Fax = $Fax;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->Fax;
    }

    /**
     * @param mixed $InfoPageID
     */
    public function setInfoPageID($InfoPageID)
    {
        $this->InfoPageID = $InfoPageID;
    }

    /**
     * @return mixed
     */
    public function getInfoPageID()
    {
        return $this->InfoPageID;
    }

    /**
     * @param mixed $Notes
     */
    public function setNotes($Notes)
    {
        $this->Notes = $Notes;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->Notes;
    }

    /**
     * @param mixed $PhoneNumber
     */
    public function setPhoneNumber($PhoneNumber)
    {
        $this->PhoneNumber = $PhoneNumber;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->PhoneNumber;
    }

    /**
     * @param mixed $RusAddress
     */
    public function setRusAddress($RusAddress)
    {
        $this->RusAddress = $RusAddress;
    }

    /**
     * @return mixed
     */
    public function getRusAddress()
    {
        return $this->RusAddress;
    }

    /**
     * @param mixed $RusName
     */
    public function setRusName($RusName)
    {
        $this->RusName = $RusName;
    }

    /**
     * @return mixed
     */
    public function getRusName()
    {
        return $this->RusName;
    }

    /**
     * @param mixed $ShortAddress
     */
    public function setShortAddress($ShortAddress)
    {
        $this->ShortAddress = $ShortAddress;
    }

    /**
     * @return mixed
     */
    public function getShortAddress()
    {
        return $this->ShortAddress;
    }

    /**
     * @param mixed $WithoutPage
     */
    public function setWithoutPage($WithoutPage)
    {
        $this->WithoutPage = $WithoutPage;
    }

    /**
     * @return mixed
     */
    public function getWithoutPage()
    {
        return $this->WithoutPage;
    }

    /**
     * @param mixed $pictures
     */
    public function setPictures(ArrayCollection $pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * @return mixed
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @param mixed $CountryCode
     */
    public function setCountryCode($CountryCode)
    {
        $this->CountryCode = $CountryCode;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->CountryCode;
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
     * @param mixed $CountryEditionCode
     */
    public function setCountryEditionCode($CountryEditionCode)
    {
        $this->CountryEditionCode = $CountryEditionCode;
    }

    /**
     * @return mixed
     */
    public function getCountryEditionCode()
    {
        return $this->CountryEditionCode;
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
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->Url;
    }

    /**
     * @param mixed $Url
     */
    public function setUrl($Url)
    {
        $this->Url = $Url;
    }

    /**
     * @return mixed
     */
    public function getApprovecode()
    {
        return $this->approvecode;
    }

    /**
     * @param mixed $approvecode
     */
    public function setApprovecode($approvecode)
    {
        $this->approvecode = $approvecode;
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

    public function getId()
    {
        return $this->InfoPageID;
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param mixed $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }
}