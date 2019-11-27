<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="DocumentRepository") @ORM\Table(name="document") */
class Document
{
    const ACTIVE_SUBSTANCE = [1, 4, 8];
    const INSTRUCTIONS = [2, 5];

	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $DocumentID;

	/** @ORM\Column(length=500) */
	protected $RusName;

	/** @ORM\Column(length=500, nullable=true) */
	protected $EngName;

	/** @ORM\Column(length=500, nullable=true) */
	protected $Name;

	/** @ORM\Column(type="text", nullable=true) */
	protected $CompiledComposition;

	/** @ORM\Column(type="integer") */
	protected $ArticleID;

	/** @ORM\Column(length=4) */
	protected $YearEdition;

	/** @ORM\Column(type="datetime") @Gedmo\Timestampable(on="update") */
	protected $DateOfIncludingText;

	/** @ORM\Column(type="datetime", nullable=true) */
	protected $DateTextModified;

	/** @ORM\Column(length=255, nullable=true) */
	protected $Elaboration;

	/** @ORM\Column(type="text", nullable=true) */
	protected $CompaniesDescription;

	/** @ORM\Column(type="text", nullable=true) */
	protected $ClPhGrDescription;

	/** @ORM\Column(type="text", nullable=true) */
	protected $ClPhGrName;

	/** @ORM\Column(type="text", nullable=true) */
	protected $PhInfluence;

	/** @ORM\Column(type="text", nullable=true) */
	protected $PhKinetics;

	/** @ORM\Column(type="text", nullable=true) */
	protected $Dosage;

	/** @ORM\Column(type="text", nullable=true) */
	protected $OverDosage;

	/** @ORM\Column(type="text", nullable=true) */
	protected $Interaction;

	/** @ORM\Column(type="text", nullable=true) */
	protected $Lactation;

	/** @ORM\Column(type="text", nullable=true) */
	protected $SideEffects;

	/** @ORM\Column(type="text", nullable=true) */
	protected $StorageCondition;

	/** @ORM\Column(type="text", nullable=true) */
	protected $Indication;

	/** @ORM\Column(type="text", nullable=true) */
	protected $SpecialInstruction;

	/** @ORM\Column(type="text", nullable=true) */
	protected $ContraIndication;

	/** @ORM\Column(type="boolean") */
	protected $ShowGenericsOnlyInGNList = false;

	/** @ORM\Column(type="boolean") */
	protected $NewForCurrentEdition = false;

	/** @ORM\Column(length=10) */
	protected $CountryEditionCode = 'RUS';

	/** @ORM\Column(type="boolean") */
	protected $IsApproved = false;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $CountOfColorPhoto;

	/** @ORM\Column(type="text", nullable=true) */
	protected $PharmDelivery;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $AnimalTypeCode;

    /** @ORM\Column(type="boolean") */
    protected $IsShortened = false;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $ed;

	/** @ORM\OneToMany(targetEntity="ProductDocument", mappedBy="DocumentID") */
	protected $productDocument;

	/**
	 * @ORM\ManyToMany(targetEntity="ClinicoPhPointers", mappedBy="documents")
	 * @ORM\JoinTable(name="document_clphpointers",
	 *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="ClPhPointerID", referencedColumnName="ClPhPointerID")})
	 */
	protected $clphPointers;

	/** @ORM\Column(length=4, nullable=true) */
	protected $PregnancyUsing;

	/** @ORM\Column(length=4, nullable=true) */
	protected $NursingUsing;

	/** @ORM\Column(type="text", nullable=true) */
	protected $RenalInsuf;

	/** @ORM\Column(length=4, nullable=true) */
	protected $RenalInsufUsing;

	/** @ORM\Column(type="text", nullable=true) */
	protected $HepatoInsuf;

	/** @ORM\Column(length=4, nullable=true) */
	protected $HepatoInsufUsing;

	/** @ORM\Column(type="boolean") */
	protected $WithoutRenalInsuf = false;

	/** @ORM\Column(type="boolean") */
	protected $WithoutHepatoInsuf = false;

	/** @ORM\Column(type="text", nullable=true) */
	protected $ElderlyInsuf;

	/** @ORM\Column(length=4, nullable=true) */
	protected $ElderlyInsufUsing;

	/** @ORM\Column(type="text", nullable=true) */
	protected $ChildInsuf;

	/** @ORM\Column(length=4, nullable=true) */
	protected $ChildInsufUsing;

	/**
	 * @ORM\ManyToMany(targetEntity="Molecule", inversedBy="documents")
	 * @ORM\JoinTable(name="molecule_document",
	 *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="MoleculeID", referencedColumnName="MoleculeID")})
	 */
	protected $molecules;

	/** @ORM\OneToMany(targetEntity="Product", mappedBy="document") */
	protected $products;

	/**
	 * @ORM\ManyToMany(targetEntity="InfoPage", inversedBy="documents")
	 * @ORM\JoinTable(name="document_infopage",
	 *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")})
	 */
	protected $infoPages;

	/** @ORM\OneToMany(targetEntity="PharmPortfolio", mappedBy="DocumentID") */
	protected $portfolios;

    /** @ORM\Column(type="boolean") */
    protected $IsNotForSite = false;

	public function __construct()
	{
		$this->productDocument = new ArrayCollection();
		$this->clphPointers    = new ArrayCollection();
		$this->infoPages       = new ArrayCollection();
		$this->molecules       = new ArrayCollection();
		$this->products        = new ArrayCollection();
	}

	public function __toString()
	{
		return empty($this->RusName) ? '' : $this->RusName;
	}

    /**
     * Является активным веществом
     * @return bool
     */
    public function isActiveSubstance()
    {
        $articleID = $this->getArticleID();
        if($articleID &&  in_array($articleID, self::ACTIVE_SUBSTANCE)) {
            return true;
        }
        return false;
    }

    /**
     * Является инструкцией
     * @return bool
     */
    public function isInstruction()
    {
        $articleID = $this->getArticleID();
        if($articleID &&  in_array($articleID, self::INSTRUCTIONS)) {
            return true;
        }
        return false;
    }

	/**
	 * @param mixed $ArticleID
	 */
	public function setArticleID($ArticleID)
	{
		$this->ArticleID = $ArticleID;
	}

	/**
	 * @return mixed
	 */
	public function getArticleID()
	{
		return $this->ArticleID;
	}

	/**
	 * @param mixed $ClPhGrDescription
	 */
	public function setClPhGrDescription($ClPhGrDescription)
	{
		$this->ClPhGrDescription = $ClPhGrDescription;
	}

	/**
	 * @return mixed
	 */
	public function getClPhGrDescription()
	{
		return $this->ClPhGrDescription;
	}

	/**
	 * @param mixed $CompaniesDescription
	 */
	public function setCompaniesDescription($CompaniesDescription)
	{
		$this->CompaniesDescription = $CompaniesDescription;
	}

	/**
	 * @return mixed
	 */
	public function getCompaniesDescription()
	{
		return $this->CompaniesDescription;
	}

	/**
	 * @param mixed $CompiledComposition
	 */
	public function setCompiledComposition($CompiledComposition)
	{
		$this->CompiledComposition = $CompiledComposition;
	}

	/**
	 * @return mixed
	 */
	public function getCompiledComposition()
	{
		return $this->CompiledComposition;
	}

	/**
	 * @param mixed $CountOfColorPhoto
	 */
	public function setCountOfColorPhoto($CountOfColorPhoto)
	{
		$this->CountOfColorPhoto = $CountOfColorPhoto;
	}

	/**
	 * @return mixed
	 */
	public function getCountOfColorPhoto()
	{
		return $this->CountOfColorPhoto;
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
	 * @param mixed $DocumentID
	 */
	public function setDocumentID($DocumentID)
	{
		$this->DocumentID = $DocumentID;
	}

	/**
	 * @return mixed
	 */
	public function getDocumentID()
	{
		return $this->DocumentID;
	}

	/**
	 * @param mixed $Dosage
	 */
	public function setDosage($Dosage)
	{
		$this->Dosage = $Dosage;
	}

	/**
	 * @return mixed
	 */
	public function getDosage()
	{
		return $this->Dosage;
	}

	/**
	 * @param mixed $Elaboration
	 */
	public function setElaboration($Elaboration)
	{
		$this->Elaboration = $Elaboration;
	}

	/**
	 * @return mixed
	 */
	public function getElaboration()
	{
		return $this->Elaboration;
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
	 * @param mixed $Indication
	 */
	public function setIndication($Indication)
	{
		$this->Indication = $Indication;
	}

	/**
	 * @return mixed
	 */
	public function getIndication()
	{
		return $this->Indication;
	}

	/**
	 * @param mixed $IsApproved
	 */
	public function setIsApproved($IsApproved)
	{
		$this->IsApproved = $IsApproved;
	}

	/**
	 * @return mixed
	 */
	public function getIsApproved()
	{
		return $this->IsApproved;
	}

	/**
	 * @param mixed $NewForCurrentEdition
	 */
	public function setNewForCurrentEdition($NewForCurrentEdition)
	{
		$this->NewForCurrentEdition = $NewForCurrentEdition;
	}

	/**
	 * @return mixed
	 */
	public function getNewForCurrentEdition()
	{
		return $this->NewForCurrentEdition;
	}

	/**
	 * @param mixed $PhInfluence
	 */
	public function setPhInfluence($PhInfluence)
	{
		$this->PhInfluence = $PhInfluence;
	}

	/**
	 * @return mixed
	 */
	public function getPhInfluence()
	{
		return $this->PhInfluence;
	}

	/**
	 * @param mixed $PharmDelivery
	 */
	public function setPharmDelivery($PharmDelivery)
	{
		$this->PharmDelivery = $PharmDelivery;
	}

	/**
	 * @return mixed
	 */
	public function getPharmDelivery()
	{
		return $this->PharmDelivery;
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
	 * @param mixed $ShowGenericsOnlyInGNList
	 */
	public function setShowGenericsOnlyInGNList($ShowGenericsOnlyInGNList)
	{
		$this->ShowGenericsOnlyInGNList = $ShowGenericsOnlyInGNList;
	}

	/**
	 * @return mixed
	 */
	public function getShowGenericsOnlyInGNList()
	{
		return $this->ShowGenericsOnlyInGNList;
	}

	/**
	 * @param mixed $SideEffects
	 */
	public function setSideEffects($SideEffects)
	{
		$this->SideEffects = $SideEffects;
	}

	/**
	 * @return mixed
	 */
	public function getSideEffects()
	{
		return $this->SideEffects;
	}

	/**
	 * @param mixed $SpecialInstruction
	 */
	public function setSpecialInstruction($SpecialInstruction)
	{
		$this->SpecialInstruction = $SpecialInstruction;
	}

	/**
	 * @return mixed
	 */
	public function getSpecialInstruction()
	{
		return $this->SpecialInstruction;
	}

	/**
	 * @param mixed $StorageCondition
	 */
	public function setStorageCondition($StorageCondition)
	{
		$this->StorageCondition = $StorageCondition;
	}

	/**
	 * @return mixed
	 */
	public function getStorageCondition()
	{
		return $this->StorageCondition;
	}

    /**
     * @return mixed
     */
    public function getStorageConditionSource()
    {
        if($this->getStorageTime()) {
            return str_replace($this->getStorageTime(), "", $this->getStorageCondition());
        }
        return $this->getStorageCondition();
    }

    /**
     * @return mixed
     */
    public function getStorageTime()
    {
        if($this->StorageCondition) {
            preg_match('/Срок годности(.*)/', $this->StorageCondition, $matches);
            if(isset($matches[0]) && $matches[0]) {
                return $matches[0];
            }
        }

        return "";
    }

	/**
	 * @param mixed $YearEdition
	 */
	public function setYearEdition($YearEdition)
	{
		$this->YearEdition = $YearEdition;
	}

	/**
	 * @return mixed
	 */
	public function getYearEdition()
	{
		return $this->YearEdition;
	}

	/**
	 * @param mixed $ed
	 */
	public function setEd($ed)
	{
		$this->ed = $ed;
	}

	/**
	 * @return mixed
	 */
	public function getEd()
	{
		return $this->ed;
	}

	/**
	 * @param mixed $productDocument
	 */
	public function setProductDocument(ArrayCollection $productDocument)
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
	 * @param mixed $clphPointers
	 */
	public function setClphPointers(ArrayCollection $clphPointers)
	{
		$this->clphPointers = $clphPointers;
	}

	/**
	 * @return mixed
	 */
	public function getClphPointers()
	{
		return $this->clphPointers;
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
	 * @param mixed $ClPhGrName
	 */
	public function setClPhGrName($ClPhGrName)
	{
		$this->ClPhGrName = $ClPhGrName;
	}

	/**
	 * @return mixed
	 */
	public function getClPhGrName()
	{
		return $this->ClPhGrName;
	}

	/**
	 * @param mixed $ContraIndication
	 */
	public function setContraIndication($ContraIndication)
	{
		$this->ContraIndication = $ContraIndication;
	}

	/**
	 * @return mixed
	 */
	public function getContraIndication()
	{
		return $this->ContraIndication;
	}

	/**
	 * @return mixed
	 */
	public function getPhKinetics()
	{
		return $this->PhKinetics;
	}

	/**
	 * @param mixed $PhKinetics
	 */
	public function setPhKinetics($PhKinetics)
	{
		$this->PhKinetics = $PhKinetics;
	}

	/**
	 * @return mixed
	 */
	public function getOverDosage()
	{
		return $this->OverDosage;
	}

	/**
	 * @param mixed $OverDosage
	 */
	public function setOverDosage($OverDosage)
	{
		$this->OverDosage = $OverDosage;
	}

	/**
	 * @return mixed
	 */
	public function getInteraction()
	{
		return $this->Interaction;
	}

	/**
	 * @param mixed $Interaction
	 */
	public function setInteraction($Interaction)
	{
		$this->Interaction = $Interaction;
	}

	/**
	 * @return mixed
	 */
	public function getLactation()
	{
		return $this->Lactation;
	}

	/**
	 * @param mixed $Lactation
	 */
	public function setLactation($Lactation)
	{
		$this->Lactation = $Lactation;
	}

	/**
	 * @return mixed
	 */
	public function getPregnancyUsing()
	{
		return $this->PregnancyUsing;
	}

	/**
	 * @param mixed $PregnancyUsing
	 */
	public function setPregnancyUsing($PregnancyUsing)
	{
		$this->PregnancyUsing = $PregnancyUsing;
	}

	/**
	 * @return mixed
	 */
	public function getNursingUsing()
	{
		return $this->NursingUsing;
	}

	/**
	 * @param mixed $NursingUsing
	 */
	public function setNursingUsing($NursingUsing)
	{
		$this->NursingUsing = $NursingUsing;
	}

	/**
	 * @return mixed
	 */
	public function getRenalInsuf()
	{
		return $this->RenalInsuf;
	}

	/**
	 * @param mixed $RenalInsuf
	 */
	public function setRenalInsuf($RenalInsuf)
	{
		$this->RenalInsuf = $RenalInsuf;
	}

	/**
	 * @return mixed
	 */
	public function getRenalInsufUsing()
	{
		return $this->RenalInsufUsing;
	}

	/**
	 * @param mixed $RenalInsufUsing
	 */
	public function setRenalInsufUsing($RenalInsufUsing)
	{
		$this->RenalInsufUsing = $RenalInsufUsing;
	}

	/**
	 * @return mixed
	 */
	public function getHepatoInsuf()
	{
		return $this->HepatoInsuf;
	}

	/**
	 * @param mixed $HepatoInsuf
	 */
	public function setHepatoInsuf($HepatoInsuf)
	{
		$this->HepatoInsuf = $HepatoInsuf;
	}

	/**
	 * @return mixed
	 */
	public function getHepatoInsufUsing()
	{
		return $this->HepatoInsufUsing;
	}

	/**
	 * @param mixed $HepatoInsufUsing
	 */
	public function setHepatoInsufUsing($HepatoInsufUsing)
	{
		$this->HepatoInsufUsing = $HepatoInsufUsing;
	}

	/**
	 * @return mixed
	 */
	public function getWithoutRenalInsuf()
	{
		return $this->WithoutRenalInsuf;
	}

	/**
	 * @param mixed $WithoutRenalInsuf
	 */
	public function setWithoutRenalInsuf($WithoutRenalInsuf)
	{
		$this->WithoutRenalInsuf = $WithoutRenalInsuf;
	}

	/**
	 * @return mixed
	 */
	public function getWithoutHepatoInsuf()
	{
		return $this->WithoutHepatoInsuf;
	}

	/**
	 * @param mixed $WithoutHepatoInsuf
	 */
	public function setWithoutHepatoInsuf($WithoutHepatoInsuf)
	{
		$this->WithoutHepatoInsuf = $WithoutHepatoInsuf;
	}

	/**
	 * @return mixed
	 */
	public function getElderlyInsuf()
	{
		return $this->ElderlyInsuf;
	}

	/**
	 * @param mixed $ElderlyInsuf
	 */
	public function setElderlyInsuf($ElderlyInsuf)
	{
		$this->ElderlyInsuf = $ElderlyInsuf;
	}

	/**
	 * @return mixed
	 */
	public function getElderlyInsufUsing()
	{
		return $this->ElderlyInsufUsing;
	}

	/**
	 * @param mixed $ElderlyInsufUsing
	 */
	public function setElderlyInsufUsing($ElderlyInsufUsing)
	{
		$this->ElderlyInsufUsing = $ElderlyInsufUsing;
	}

	/**
	 * @return mixed
	 */
	public function getChildInsuf()
	{
		return $this->ChildInsuf;
	}

	/**
	 * @param mixed $ChildInsuf
	 */
	public function setChildInsuf($ChildInsuf)
	{
		$this->ChildInsuf = $ChildInsuf;
	}

	/**
	 * @return mixed
	 */
	public function getChildInsufUsing()
	{
		return $this->ChildInsufUsing;
	}

	/**
	 * @param mixed $ChildInsufUsing
	 */
	public function setChildInsufUsing($ChildInsufUsing)
	{
		$this->ChildInsufUsing = $ChildInsufUsing;
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
	 * @return mixed
	 */
	public function getPortfolios()
	{
		return $this->portfolios;
	}

	/**
	 * @param mixed $portfolios
	 */
	public function setPortfolios($portfolios)
	{
		$this->portfolios = $portfolios;
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
    public function getAnimalTypeCode()
    {
        return $this->AnimalTypeCode;
    }

    /**
     * @param mixed $AnimalTypeCode
     */
    public function setAnimalTypeCode($AnimalTypeCode)
    {
        $this->AnimalTypeCode = $AnimalTypeCode;
    }

    /**
     * @return mixed
     */
    public function getIsShortened()
    {
        return $this->IsShortened;
    }

    /**
     * @param mixed $IsShortened
     */
    public function setIsShortened($IsShortened)
    {
        $this->IsShortened = $IsShortened;
    }
}