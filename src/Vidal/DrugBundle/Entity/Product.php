<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="ProductRepository") @ORM\Table(name="product") @FileStore\Uploadable */
class Product
{
    const TYPE_BAD = 'BAD';
    const TYPE_MI = 'MI';
    const TYPE_PARA = 'PARA';
    const TYPE_DRUG = 'DRUG';
    const TYPE_NUTR = 'NUTR';
    const TYPE_COSM = 'COSM';

    /** @ORM\Id @ORM\Column(type="integer") */
    protected $ProductID;

    /** @ORM\Column(length=255, nullable=true) */
    protected $url;

    /** @ORM\Column(length=255, nullable=true) */
    protected $uri;

    /** @ORM\Column(length=255, nullable=true) */
    protected $seoTitle;

    /** @ORM\Column(length=500, nullable=true) */
    protected $seoDescription;

    /** @ORM\Column(length=1000, nullable=true) */
    protected $description;

    /** @ORM\Column(length=500, nullable=true) */
    protected $RusName;

    /** @ORM\Column(length=255, nullable=true) */
    protected $registrationcountrycode;

    /** @ORM\Column(length=255, nullable=true) */
    protected $registrationtypecode;

    /**
     * Колонка с именами препаратов БЕЗ лишних символов
     * @ORM\Column(length=500, nullable=true)
     */
    protected $RusName2;

    /** @ORM\Column(length=500, nullable=true) */
    protected $EngName;

    /** @ORM\Column(length=500, nullable=true) */
    protected $Name;

    /** @ORM\Column(type="boolean") */
    protected $NonPrescriptionDrug = false;

    /** @ORM\Column(length=10) */
    protected $CountryEditionCode = 'RUS';

    /** @ORM\Column(length=50, nullable=true) */
    protected $RegistrationDate;

    /** @ORM\Column(length=50, nullable=true) */
    protected $DateOfCloseRegistration;

    /** @ORM\Column(length=50, nullable=true) */
    protected $RegistrationNumber;

    /** @ORM\Column(type="boolean") */
    protected $PPR = false;

    /** @ORM\Column(length=255) */
    protected $ZipInfo;

    /** @ORM\Column(type="text", nullable=true) */
    protected $Composition;

    /** @ORM\Column(type="datetime") @Gedmo\Timestampable(on="update") */
    protected $DateOfIncludingText;

    /**
     * @ORM\Column(length=10)
     */
    protected $ProductTypeCode;

    /** @ORM\Column(type="boolean") */
    protected $ItsMultiProduct = false;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $BelongMultiProductID;

    /**
     * @ORM\ManyToOne(targetEntity="MarketStatus", inversedBy="products")
     * @ORM\JoinColumn(name="MarketStatusID", referencedColumnName="MarketStatusID")
     */
    protected $MarketStatusID;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $CheckingRegDate;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $Personal;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $ParentID;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $MainID;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $MainIDManual;

    /** @ORM\Column(type="boolean") */
    protected $IsNotForSite = false;

    /** @ORM\Column(type="boolean") */
    protected $IsWithoutOC = false;

    /** @ORM\Column(length=50, nullable=true) */
    protected $DateOfReRegistration;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $GNVLS = false;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $DLO = false;

    /** @ORM\Column(length=10, nullable=true) */
    protected $List_AB;

    /** @ORM\Column(length=10, nullable=true) */
    protected $List_PKKN;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $StrongMeans = false;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $Poison = false;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $MinAs;

    /** @ORM\Column(length=50, nullable=true) */
    protected $ValidPeriod;

    /** @ORM\Column(type="text", nullable=true) */
    protected $StrCond;

    /**
     * @ORM\ManyToMany(targetEntity="InfoPage", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="product_infopage",
     *      joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")})
     */
    protected $infoPages;

    /**
     * @ORM\ManyToMany(targetEntity="ATC", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="product_atc",
     *      joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ATCcode", referencedColumnName="ATCCode")})
     */
    protected $atcCodes;

    /**
     * @ORM\ManyToMany(targetEntity="ClPhGroups", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="product_clphgroups",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ClPhGroupsID", referencedColumnName="ClPhGroupsID")})
     */
    protected $clphGroups;

    /**
     * @ORM\OneToMany(targetEntity="ProductCompany", mappedBy="ProductID", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"ItsMainCompany" = "DESC", "Ranking" = "ASC"})
     */
    protected $productCompany;

    /**
     * @ORM\ManyToMany(targetEntity="MoleculeName", inversedBy="products")
     * @ORM\JoinTable(name="product_moleculename",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="MoleculeNameID", referencedColumnName="MoleculeNameID")})
     */
    protected $moleculeNames;

    /**
     * @ORM\ManyToMany(targetEntity="PhThGroups", inversedBy="products", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="product_phthgrp",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="PhThGroupsID", referencedColumnName="id")})
     */
    protected $phthgroups;

    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="products")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="DocumentID")
     */
    protected $document;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $document_manual_id;

    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="products_all")
     * @ORM\JoinColumn(name="document_all_id", referencedColumnName="DocumentID")
     */
    protected $document_all;

    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="products_merge")
     * @ORM\JoinColumn(name="document_merge_id", referencedColumnName="DocumentID")
     */
    protected $document_merge;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="product_photo")
     */
    protected $photo;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="product_photo")
     */
    protected $photo2;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="product_photo")
     */
    protected $photo3;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="product_photo")
     */
    protected $photo4;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="product_photo")
     */
    protected $photo5;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="product_photo")
     */
    protected $photo6;

    /** @ORM\Column(type="boolean") */
    protected $inactive = false;

    /** @ORM\Column(type="boolean") */
    protected $testMode = false;

    /** @ORM\Column(length=255, nullable=true) */
    protected $testTitle;

    /**
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="products")
     * @ORM\JoinTable(name="article_product",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $articles;

    /**
     * @ORM\ManyToMany(targetEntity="Art", mappedBy="products")
     * @ORM\JoinTable(name="art_product",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="art_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $arts;

    /**
     * @ORM\ManyToMany(targetEntity="Ads", mappedBy="products")
     * @ORM\JoinTable(name="ads_product",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ads_id", referencedColumnName="id")})
     */
    protected $ads;

    /**
     * @ORM\ManyToMany(targetEntity="Publication", mappedBy="products")
     * @ORM\JoinTable(name="publication_product",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $publications;

    /**
     * @ORM\ManyToMany(targetEntity="PharmArticle", mappedBy="products")
     * @ORM\JoinTable(name="pharm_article_product",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")})
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $pharmArticles;

    /** @ORM\Column(type="boolean") */
    protected $hidePhoto = false;

    /**
     * @ORM\ManyToMany(targetEntity="Rigla", mappedBy="products")
     * @ORM\JoinTable(name="rigla_product",
     *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="rigla_id", referencedColumnName="id")})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $riglas;

    /** @ORM\Column(type="string", nullable=true) */
    protected $shortZipInfo;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="ProductID")
     */
    protected $parent;

    /** @ORM\OneToMany(targetEntity="Product", mappedBy="parent") */
    protected $children;

    /** @ORM\Column(type="boolean") */
    protected $hasChildren = false;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $ga_pageviews;

    /** @ORM\Column(type="text", nullable=true) */
    protected $forms;

    /** @ORM\Column(type="text", nullable=true) */
    protected $formsGrouped;

	/** @ORM\Column(type="text", nullable=true) */
	protected $pictures;

    /** @ORM\Column(type="integer", nullable=true) */
	protected $countPictures = 0;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $hasChildrenParentID = false;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $hasChildrenMainID = false;

    /** @ORM\Column(type="string", nullable=true) */
    protected $molecules = false;

    /** @ORM\Column(type="text", nullable=true) */
    protected $atcs;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $multiForm = false;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $redirectId = 0;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $analogsFull = 0;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $analogsGroup = 0;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $analogsNosology = 0;

    public function __construct()
    {
        $this->infoPages = new ArrayCollection();
        $this->atcCodes = new ArrayCollection();
        $this->clphGroups = new ArrayCollection();
        $this->productCompany = new ArrayCollection();
        $this->moleculeNames = new ArrayCollection();
        $this->phthgroups = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->arts = new ArrayCollection();
        $this->ads = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->pharmArticles = new ArrayCollection();
        $this->riglas = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString()
    {
        return empty($this->RusName) ? '' : $this->RusName;
    }

    public function getId()
    {
        return $this->ProductID;
    }

    /**
     * @param mixed $BelongMultiProductID
     */
    public function setBelongMultiProductID($BelongMultiProductID)
    {
        $this->BelongMultiProductID = $BelongMultiProductID;
    }

    /**
     * @return mixed
     */
    public function getBelongMultiProductID()
    {
        return $this->BelongMultiProductID;
    }

    /**
     * @param mixed $CheckingRegDate
     */
    public function setCheckingRegDate($CheckingRegDate)
    {
        $this->CheckingRegDate = $CheckingRegDate;
    }

    /**
     * @return mixed
     */
    public function getCheckingRegDate()
    {
        return $this->CheckingRegDate;
    }

    /**
     * @param mixed $Composition
     */
    public function setComposition($Composition)
    {
        $this->Composition = $Composition;
    }

    /**
     * @return mixed
     */
    public function getComposition()
    {
        return $this->Composition;
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
     * @param mixed $DLO
     */
    public function setDLO($DLO)
    {
        $this->DLO = $DLO;
    }

    /**
     * @return mixed
     */
    public function getDLO()
    {
        return $this->DLO;
    }

    /**
     * @param mixed $DateOfCloseRegistration
     */
    public function setDateOfCloseRegistration($DateOfCloseRegistration)
    {
        $this->DateOfCloseRegistration = $DateOfCloseRegistration;
    }

    /**
     * @return mixed
     */
    public function getDateOfCloseRegistration()
    {
        return $this->DateOfCloseRegistration;
    }

    /**
     * @param mixed $DateOfIncludingText
     */
    public function setDateOfIncludingText($DateOfIncludingText)
    {
        $this->DateOfIncludingText = $DateOfIncludingText;
    }

    /**
     * @return mixed
     */
    public function getDateOfIncludingText()
    {
        return $this->DateOfIncludingText;
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
     * @param mixed $GNVLS
     */
    public function setGNVLS($GNVLS)
    {
        $this->GNVLS = $GNVLS;
    }

    /**
     * @return mixed
     */
    public function getGNVLS()
    {
        return $this->GNVLS;
    }

    /**
     * @param mixed $ItsMultiProduct
     */
    public function setItsMultiProduct($ItsMultiProduct)
    {
        $this->ItsMultiProduct = $ItsMultiProduct;
    }

    /**
     * @return mixed
     */
    public function getItsMultiProduct()
    {
        return $this->ItsMultiProduct;
    }

    /**
     * @param mixed $List_AB
     */
    public function setListAB($List_AB)
    {
        $this->List_AB = $List_AB;
    }

    /**
     * @return mixed
     */
    public function getListAB()
    {
        return $this->List_AB;
    }

    /**
     * @param mixed $List_PKKN
     */
    public function setListPKKN($List_PKKN)
    {
        $this->List_PKKN = $List_PKKN;
    }

    /**
     * @return mixed
     */
    public function getListPKKN()
    {
        return $this->List_PKKN;
    }

    /**
     * @param mixed $MarketStatusID
     */
    public function setMarketStatusID($MarketStatusID)
    {
        $this->MarketStatusID = $MarketStatusID;
    }

    /**
     * @return mixed
     */
    public function getMarketStatusID()
    {
        return $this->MarketStatusID;
    }

    /**
     * @param mixed $MinAs
     */
    public function setMinAs($MinAs)
    {
        $this->MinAs = $MinAs;
    }

    /**
     * @return mixed
     */
    public function getMinAs()
    {
        return $this->MinAs;
    }

    /**
     * @param mixed $NonPrescriptionDrug
     */
    public function setNonPrescriptionDrug($NonPrescriptionDrug)
    {
        $this->NonPrescriptionDrug = $NonPrescriptionDrug;
    }

    /**
     * @return mixed
     */
    public function getNonPrescriptionDrug()
    {
        return $this->NonPrescriptionDrug;
    }

    /**
     * @param mixed $PPR
     */
    public function setPPR($PPR)
    {
        $this->PPR = $PPR;
    }

    /**
     * @return mixed
     */
    public function getPPR()
    {
        return $this->PPR;
    }

    /**
     * @param mixed $Personal
     */
    public function setPersonal($Personal)
    {
        $this->Personal = $Personal;
    }

    /**
     * @return mixed
     */
    public function getPersonal()
    {
        return $this->Personal;
    }

    /**
     * @param mixed $Poison
     */
    public function setPoison($Poison)
    {
        $this->Poison = $Poison;
    }

    /**
     * @return mixed
     */
    public function getPoison()
    {
        return $this->Poison;
    }

    /**
     * @param mixed $ProductID
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
    }

    /**
     * @return mixed
     */
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param mixed $ProductTypeCode
     */
    public function setProductTypeCode($ProductTypeCode)
    {
        $this->ProductTypeCode = $ProductTypeCode;
    }

    /**
     * @return mixed
     */
    public function getProductTypeCode()
    {
        return $this->ProductTypeCode;
    }

    /**
     * @param mixed $RegistrationDate
     */
    public function setRegistrationDate($RegistrationDate)
    {
        $this->RegistrationDate = $RegistrationDate;
    }

    /**
     * @return mixed
     */
    public function getRegistrationDate()
    {
        return $this->RegistrationDate;
    }

    /**
     * @param mixed $RegistrationNumber
     */
    public function setRegistrationNumber($RegistrationNumber)
    {
        $this->RegistrationNumber = $RegistrationNumber;
    }

    /**
     * @return mixed
     */
    public function getRegistrationNumber()
    {
        return $this->RegistrationNumber;
    }

    /**
     * @param mixed $RusName
     */
    public function setRusName($RusName)
    {
        $this->RusName = $RusName;

        $rusName2 = str_replace('<SUP>', '', $RusName);
        $rusName2 = str_replace('</SUP>', '', $rusName2);
        $rusName2 = str_replace('<SUB>', '', $rusName2);
        $rusName2 = str_replace('</SUB>', '', $rusName2);
        $rusName2 = str_replace('<BR/>', '', $rusName2);
        $rusName2 = str_replace('<BR />', '', $rusName2);
        $rusName2 = str_replace('&reg;', '', $rusName2);
        $rusName2 = str_replace('&amp;', '', $rusName2);
        $rusName2 = str_replace('&trade;', '', $rusName2);
        $rusName2 = str_replace('&alpha;', '', $rusName2);
        $rusName2 = str_replace('&beta;', '', $rusName2);
        $rusName2 = str_replace('&plusmn;', '', $rusName2);
        $rusName2 = str_replace('  ', '', $rusName2);

        $this->RusName2 = $rusName2;
    }

    /**
     * @return mixed
     */
    public function getRusNameOriginnal()
    {
        return $this->RusName;
    }

    /**
     * @return mixed
     */
    public function getRusName()
    {
        if($this->getHasChildrenMainID() && $this->getMultiForm() && $this->getDocumentMerge() && 
            !$this->getDocumentMerge()->isActiveSubstance()
        ) {
            return $this->getDocumentMerge()->getRusNameWithElaboration();
        }

        return $this->RusName;
    }

    /**
     * @param mixed $StrCond
     */
    public function setStrCond($StrCond)
    {
        $this->StrCond = $StrCond;
    }

    /**
     * @return mixed
     */
    public function getStrCond()
    {
        return $this->StrCond;
    }

    /**
     * @param mixed $StrongMeans
     */
    public function setStrongMeans($StrongMeans)
    {
        $this->StrongMeans = $StrongMeans;
    }

    /**
     * @return mixed
     */
    public function getStrongMeans()
    {
        return $this->StrongMeans;
    }

    /**
     * @param mixed $ValidPeriod
     */
    public function setValidPeriod($ValidPeriod)
    {
        $this->ValidPeriod = $ValidPeriod;
    }

    /**
     * @return mixed
     */
    public function getValidPeriod()
    {
        return $this->ValidPeriod;
    }

    /**
     * @param mixed $ZipInfo
     */
    public function setZipInfo($ZipInfo)
    {
        $this->ZipInfo = $ZipInfo;
    }

    /**
     * @return mixed
     */
    public function getZipInfo()
    {
        return $this->ZipInfo;
    }

    /**
     * @param mixed $atcCodes
     */
    public function setAtcCodes(ArrayCollection $atcCodes)
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
     * @param mixed $clphGroups
     */
    public function setClphGroups(ArrayCollection $clphGroups)
    {
        $this->clphGroups = $clphGroups;
    }

    /**
     * @return mixed
     */
    public function getClphGroups()
    {
        return $this->clphGroups;
    }

    /**
     * @param mixed $productCompany
     */
    public function setProductCompany(ArrayCollection $productCompany)
    {
        $this->productCompany = $productCompany;
    }

    /**
     * @return mixed
     */
    public function getProductCompany()
    {
        return $this->productCompany;
    }

    /**
     * @param mixed $moleculeNames
     */
    public function setMoleculeNames(ArrayCollection $moleculeNames)
    {
        $this->moleculeNames = $moleculeNames;
    }

    /**
     * @return mixed
     */
    public function getMoleculeNames()
    {
        return $this->moleculeNames;
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
     * @param mixed $phthgroups
     */
    public function setPhthgroups($phthgroups)
    {
        $this->phthgroups = $phthgroups;
    }

    /**
     * @return mixed
     */
    public function getPhthgroups()
    {
        return $this->phthgroups;
    }

    /**
     * @param mixed $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    /**
     * @return mixed
     */
    public function getDocument()
    {
        return $this->document;
    }

    public function addProductCompany(ProductCompany $pc)
    {
        $pc->setProductID($this);
        $this->productCompany[] = $pc;
    }

    public function removeProductCompany(ProductCompany $pc)
    {
        $this->productCompany->removeElement($pc);
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

    public function isBAD()
    {
        return $this->ProductTypeCode == 'BAD';
    }

    public function isNutr()
    {
        return $this->ProductTypeCode == 'NUTR';
    }

    public function isMI()
    {
        return $this->ProductTypeCode == 'MI';
    }

    public function isCosm()
    {
        return $this->ProductTypeCode == 'COSM';
    }

    public function isBadMi()
    {
        return $this->ProductTypeCode == 'BAD' || $this->ProductTypeCode == 'MI';
    }

    /**
     * @param mixed $inactive
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
    }

    /**
     * @return mixed
     */
    public function getInactive()
    {
        return $this->inactive;
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

    public function isValid()
    {
        if ($ms = $this->getMarketStatusID()) {
            if (!in_array($ms->getMarketStatusID(), array(1, 2, 7))) {
                return false;
            }
        }

        if (!in_array($this->getProductTypeCode(), array('DRUG', 'GOME', 'BAD'))) {
            return false;
        }

        if ($this->inactive) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $hidePhoto
     */
    public function setHidePhoto($hidePhoto)
    {
        $this->hidePhoto = $hidePhoto;
    }

    /**
     * @return mixed
     */
    public function getHidePhoto()
    {
        return $this->hidePhoto;
    }

    /**
     * @param mixed $RusName2
     */
    public function setRusName2($RusName2)
    {
        $this->RusName2 = $RusName2;
    }

    /**
     * @return mixed
     */
    public function getRusName2()
    {
        return $this->RusName2;
    }

    /**
     * @return mixed
     */
    public function getPhoto3()
    {
        return $this->photo3;
    }

    /**
     * @param mixed $photo3
     */
    public function setPhoto3($photo3)
    {
        $this->photo3 = $photo3;
    }

    /**
     * @return mixed
     */
    public function getPhoto2()
    {
        return $this->photo2;
    }

    /**
     * @param mixed $photo2
     */
    public function setPhoto2($photo2)
    {
        $this->photo2 = $photo2;
    }

    /**
     * @return mixed
     */
    public function getPhoto4()
    {
        return $this->photo4;
    }

    /**
     * @param mixed $photo4
     */
    public function setPhoto4($photo4)
    {
        $this->photo4 = $photo4;
    }

    /**
     * @return mixed
     */
    public function getPhoto5()
    {
        return $this->photo5;
    }

    /**
     * @param mixed $photo5
     */
    public function setPhoto5($photo5)
    {
        $this->photo5 = $photo5;
    }

    /**
     * @return mixed
     */
    public function getPhoto6()
    {
        return $this->photo6;
    }

    /**
     * @param mixed $photo6
     */
    public function setPhoto6($photo6)
    {
        $this->photo6 = $photo6;
    }

    /**
     * @return mixed
     */
    public function getIsNotForSite()
    {
        return $this->IsNotForSite;
    }

    /**
     * @param mixed $IsNotForSite
     */
    public function setIsNotForSite($IsNotForSite)
    {
        $this->IsNotForSite = $IsNotForSite;
    }

    /**
     * @return mixed
     */
    public function getIsWithoutOC()
    {
        return $this->IsWithoutOC;
    }

    /**
     * @param mixed $IsWithoutOC
     */
    public function setIsWithoutOC($IsWithoutOC)
    {
        $this->IsWithoutOC = $IsWithoutOC;
    }

    /**
     * @return mixed
     */
    public function getDateOfReRegistration()
    {
        return $this->DateOfReRegistration;
    }

    /**
     * @param mixed $DateOfReRegistration
     */
    public function setDateOfReRegistration($DateOfReRegistration)
    {
        $this->DateOfReRegistration = $DateOfReRegistration;
    }

    /**
     * @return mixed
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param mixed $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return mixed
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @param mixed $seoDescription
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
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

    /**
     * @return mixed
     */
    public function getTestTitle()
    {
        return $this->testTitle;
    }

    /**
     * @param mixed $testTitle
     */
    public function setTestTitle($testTitle)
    {
        $this->testTitle = $testTitle;
    }

    /**
     * @return mixed
     */
    public function getRiglas()
    {
        return $this->riglas;
    }

    /**
     * @param mixed $riglas
     */
    public function setRiglas($riglas)
    {
        $this->riglas = $riglas;
    }

    /**
     * @return mixed
     */
    public function getAds()
    {
        return $this->ads;
    }

    /**
     * @param mixed $ads
     */
    public function setAds($ads)
    {
        $this->ads = $ads;
    }

    /**
     * @return mixed
     */
    public function getParentID()
    {
        return $this->ParentID;
    }

    /**
     * @param mixed $ParentID
     */
    public function setParentID($ParentID)
    {
        $this->ParentID = $ParentID;
    }

    /**
     * @return mixed
     */
    public function getShortZipInfo()
    {
        return $this->shortZipInfo;
    }

    /**
     * @param mixed $shortZipInfo
     */
    public function setShortZipInfo($shortZipInfo)
    {
        $this->shortZipInfo = $shortZipInfo;
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
    public function getHasChildren()
    {
        return $this->hasChildren;
    }

    /**
     * @param mixed $hasChildren
     */
    public function setHasChildren($hasChildren)
    {
        $this->hasChildren = $hasChildren;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
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

    public function addInfoPage($infoPage)
    {
        $this->infoPages[] = $infoPage;

        return $this;
    }

    public function removeInfoPage($infoPage)
    {
        $this->infoPages->removeElement($infoPage);
    }

    public function addMoleculeName($moleculeName)
    {
        $this->moleculeNames[] = $moleculeName;

        return $this;
    }

    public function removeMoleculeName($moleculeName)
    {
        $this->moleculeNames->removeElement($moleculeName);

        return $this;
    }

    public function removeClPhGroups($g)
    {
        $this->clphGroups->removeElement($g);
    }

    public function addClPhGroups($g)
    {
        $this->clphGroups[] = $g;

        return $this;
    }

    public function removePhThGroups($g)
    {
        $this->phthgroups->removeElement($g);
    }

    public function addPhThGroups($g)
    {
        $this->phthgroups[] = $g;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
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
    public function getGaPageviews()
    {
        return $this->ga_pageviews;
    }

    /**
     * @param mixed $ga_pageviews
     */
    public function setGaPageviews($ga_pageviews)
    {
        $this->ga_pageviews = $ga_pageviews;
    }

    /**
     * @return mixed
     */
    public function getMainID()
    {
        return $this->MainID;
    }

    /**
     * @param mixed $MainID
     */
    public function setMainID($MainID)
    {
        $this->MainID = $MainID;
    }

    /**
     * @return mixed
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * @param mixed $forms
     */
    public function setForms($forms)
    {
        $this->forms = $forms;
    }

    /**
     * @return mixed
     */
    public function getRegistrationcountrycode()
    {
        return $this->registrationcountrycode;
    }

    /**
     * @param mixed $registrationcountrycode
     */
    public function setRegistrationcountrycode($registrationcountrycode)
    {
        $this->registrationcountrycode = $registrationcountrycode;
    }

    /**
     * @return mixed
     */
    public function getRegistrationtypecode()
    {
        return $this->registrationtypecode;
    }

    /**
     * @param mixed $registrationtypecode
     */
    public function setRegistrationtypecode($registrationtypecode)
    {
        $this->registrationtypecode = $registrationtypecode;
    }

	/**
	 * @return mixed
	 */
	public function getPictures()
	{
		return $this->pictures;
	}

	/**
	 * @param mixed $pictures
	 */
	public function setPictures($pictures)
	{
		$this->pictures = $pictures;
	}

    /**
     * @return mixed
     */
    public function getCountPictures()
    {
        return $this->countPictures;
    }

    /**
     * @param mixed $countPictures
     */
    public function setCountPictures($countPictures)
    {
        $this->countPictures = $countPictures;
    }

    /**
     * @return mixed
     */
    public function getHasChildrenParentID()
    {
        return $this->hasChildrenParentID;
    }

    /**
     * @param mixed $hasChildrenParentID
     */
    public function setHasChildrenParentID($hasChildrenParentID)
    {
        $this->hasChildrenParentID = $hasChildrenParentID;
    }

    /**
     * @return mixed
     */
    public function getMolecules()
    {
        return $this->molecules;
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
    public function getDocumentAll()
    {
        return $this->document_all;
    }

    /**
     * @param mixed $document_all
     */
    public function setDocumentAll($document_all)
    {
        $this->document_all = $document_all;
    }

    /**
     * @return mixed
     */
    public function getHasChildrenMainID()
    {
        return $this->hasChildrenMainID;
    }

    /**
     * @param mixed $hasChildrenMainID
     */
    public function setHasChildrenMainID($hasChildrenMainID)
    {
        $this->hasChildrenMainID = $hasChildrenMainID;
    }

    /**
     * @return mixed
     */
    public function getDocumentMerge()
    {
        return $this->document_merge;
    }

    /**
     * @param mixed $document_merge
     */
    public function setDocumentMerge($document_merge)
    {
        $this->document_merge = $document_merge;
    }

    /**
     * @return mixed
     */
    public function getDocumentManualId()
    {
        return $this->document_manual_id;
    }

    /**
     * @param mixed $document_manual_id
     */
    public function setDocumentManualId($document_manual_id)
    {
        $this->document_manual_id = $document_manual_id;
    }

    /**
     * @return mixed
     */
    public function getMainIDManual()
    {
        return $this->MainIDManual;
    }

    /**
     * @param mixed $MainIDManual
     */
    public function setMainIDManual($MainIDManual)
    {
        $this->MainIDManual = $MainIDManual;
    }

    /**
     * @return mixed
     */
    public function getAtcs()
    {
        $atcs = explode(' ', trim($this->atcs));

        return $atcs;
    }

    /**
     * @param mixed $atcs
     */
    public function setAtcs($atcs)
    {
        $this->atcs = $atcs;
    }

    /**
     * @return mixed
     */
    public function getMultiForm()
    {
        return $this->multiForm;
    }

    /**
     * @param mixed $multiForm
     */
    public function setMultiForm($multiForm)
    {
        $this->multiForm = $multiForm;
    }

    /**
     * @return mixed
     */
    public function getRedirectId()
    {
        return $this->redirectId;
    }

    /**
     * @param mixed $redirectId
     */
    public function setRedirectId($redirectId)
    {
        $this->redirectId = $redirectId;
    }

    /**
     * @return mixed
     */
    public function getInfoPages()
    {
        return $this->infoPages;
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
    public function getAnalogsFull()
    {
        return $this->analogsFull;
    }

    /**
     * @param mixed $analogsFull
     */
    public function setAnalogsFull($analogsFull)
    {
        $this->analogsFull = $analogsFull;
    }

    /**
     * @return mixed
     */
    public function getFormsGrouped()
    {
        return $this->formsGrouped;
    }

    /**
     * @param mixed $formsGrouped
     */
    public function setFormsGrouped($formsGrouped)
    {
        $this->formsGrouped = $formsGrouped;
    }

    /**
     * @return mixed
     */
    public function getAnalogsGroup()
    {
        return $this->analogsGroup;
    }

    /**
     * @param mixed $analogsGroup
     */
    public function setAnalogsGroup($analogsGroup)
    {
        $this->analogsGroup = $analogsGroup;
    }

    /**
     * @return mixed
     */
    public function getAnalogsNosology()
    {
        return $this->analogsNosology;
    }

    /**
     * @param mixed $analogsNosology
     */
    public function setAnalogsNosology($analogsNosology)
    {
        $this->analogsNosology = $analogsNosology;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }
}