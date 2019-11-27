<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="ProductRepository") @ORM\Table(name="product") @FileStore\Uploadable */
class Product
{
	/** @ORM\Id @ORM\Column(type="integer") */
	protected $ProductID;

	/** @ORM\Column(length=500) */
	protected $RusName;

	/** @ORM\Column(length=255, nullable=true) */
	protected $RusName2;

	/** @ORM\Column(length=500) */
	protected $EngName;

	/** @ORM\Column(length=500, nullable=true) */
	protected $Name;

	/** @ORM\Column(type="boolean") */
	protected $NonPrescriptionDrug = false;

	/**
	 * @ORM\Column(length=10)
	 */
	protected $CountryEditionCode = 'RUS';

	/** @ORM\Column(type="datetime", nullable=true) */
	protected $RegistrationDate;

	/** @ORM\Column(type="datetime", nullable=true) */
	protected $DateOfCloseRegistration;

	/** @ORM\Column(length=50, nullable=true) */
	protected $RegistrationNumber;

	/** @ORM\Column(type="boolean") */
	protected $PPR = false;

	/** @ORM\Column(length=255) */
	protected $ZipInfo;

	/** @ORM\Column(type="text", nullable=true) */
	protected $Composition;

	/** @ORM\Column(type="datetime", nullable=true) @Gedmo\Timestampable(on="update") */
	protected $DateOfIncludingText;

	/**
	 * @ORM\Column(length=10, nullable=true)
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
	protected $m;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $GNVLS;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $DLO;

	/** @ORM\Column(length=10, nullable=true) */
	protected $List_AB;

	/** @ORM\Column(length=10, nullable=true) */
	protected $List_PKKN;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $StrongMeans;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $Poison;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $MinAs;

	/** @ORM\Column(length=50, nullable=true) */
	protected $ValidPeriod;

	/** @ORM\Column(type="text", nullable=true) */
	protected $StrCond;

	/** @ORM\OneToMany(targetEntity="ProductDocument", mappedBy="ProductID") */
	protected $productDocument;

	/** @ORM\OneToMany(targetEntity="ProductCompany", mappedBy="ProductID", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $productCompany;

	/**
	 * @ORM\ManyToMany(targetEntity="MoleculeName", inversedBy="products")
	 * @ORM\JoinTable(name="product_moleculename",
	 *        joinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="MoleculeNameID", referencedColumnName="MoleculeNameID")})
	 */
	protected $moleculeNames;

	/** @ORM\Column(type="boolean") */
	protected $inactive = false;

	/**
	 * @ORM\ManyToOne(targetEntity="Document", inversedBy="products")
	 * @ORM\JoinColumn(name="document_id", referencedColumnName="DocumentID")
	 */
	protected $document;

	/** @ORM\Column(type="boolean") */
	protected $hidePhoto = false;

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

    /** @ORM\Column(type="boolean") */
    protected $IsNotForSite = false;

    /** @ORM\Column(length=50, nullable=true) */
    protected $RegistrationTypeCode;

    /** @ORM\Column(length=50, nullable=true) */
    protected $RegistrationCountryCode;

    /** @ORM\Column(length=50, nullable=true) */
    protected $RegistrationSerial;

	public function __construct()
	{
		$this->productDocument = new ArrayCollection();
		$this->productCompany  = new ArrayCollection();
		$this->moleculeNames   = new ArrayCollection();
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
	}

	/**
	 * @return mixed
	 */
	public function getRusName()
	{
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
	 * @param mixed $m
	 */
	public function setM($m)
	{
		$this->m = $m;
	}

	/**
	 * @return mixed
	 */
	public function getM()
	{
		return $this->m;
	}

	/**
	 * @param mixed $productDocument
	 */
	public function setProductDocument($productDocument)
	{
		$this->productDocument = $productDocument;
	}

	/**
	 * @return mixed
	 */
	public function getProductDocument()
	{
		return $this->productDocument;
	}

	/**
	 * @param mixed $productCompany
	 */
	public function setProductCompany($productCompany)
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
	 * @return mixed
	 */
	public function getMoleculeNames()
	{
		return $this->moleculeNames;
	}

	/**
	 * @param mixed $moleculeNames
	 */
	public function setMoleculeNames($moleculeNames)
	{
		$this->moleculeNames = $moleculeNames;
	}

	/**
	 * @return mixed
	 */
	public function getInactive()
	{
		return $this->inactive;
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
	public function getDocument()
	{
		return $this->document;
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
	public function getHidePhoto()
	{
		return $this->hidePhoto;
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
	public function getPhoto()
	{
		return $this->photo;
	}

	/**
	 * @param mixed $photo
	 */
	public function setPhoto($photo)
	{
		$this->photo = $photo;
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
	 * @return mixed
	 */
	public function getRusName2()
	{
		return $this->RusName2;
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
    public function getRegistrationTypeCode()
    {
        return $this->RegistrationTypeCode;
    }

    /**
     * @param mixed $RegistrationTypeCode
     */
    public function setRegistrationTypeCode($RegistrationTypeCode)
    {
        $this->RegistrationTypeCode = $RegistrationTypeCode;
    }

    /**
     * @return mixed
     */
    public function getRegistrationCountryCode()
    {
        return $this->RegistrationCountryCode;
    }

    /**
     * @param mixed $RegistrationCountryCode
     */
    public function setRegistrationCountryCode($RegistrationCountryCode)
    {
        $this->RegistrationCountryCode = $RegistrationCountryCode;
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
    public function getRegistrationSerial()
    {
        return $this->RegistrationSerial;
    }

    /**
     * @param mixed $RegistrationSerial
     */
    public function setRegistrationSerial($RegistrationSerial)
    {
        $this->RegistrationSerial = $RegistrationSerial;
    }
}