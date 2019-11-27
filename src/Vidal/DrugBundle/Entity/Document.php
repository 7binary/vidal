<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="DocumentRepository") @ORM\Table(name="document") */
class Document
{
    const ACTIVE_SUBSTANCE = [1, 4, 8];
    const INSTRUCTIONS = [2, 5];

    /** @ORM\Id @ORM\Column(type="integer") */
    protected $DocumentID;

    /** @ORM\Column(length=500) */
    protected $RusName;

    /** @ORM\Column(length=500) */
    protected $EngName;

    /** @ORM\Column(length=500, nullable=true) */
    protected $Name;

    /** @ORM\Column(type="text", nullable=true) */
    protected $CompiledComposition;

    /** @ORM\Column(type="integer") */
    protected $ArticleID;

    /** @ORM\Column(length=4) */
    protected $YearEdition;

    /** @ORM\Column(type="datetime", nullable=true) */
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
    protected $ContraIndication;

    /** @ORM\Column(type="text", nullable=true) */
    protected $SpecialInstruction;

    /** @ORM\Column(type="boolean") */
    protected $ShowGenericsOnlyInGNList = false;

    /** @ORM\Column(type="boolean") */
    protected $IsShortened = false;

    /** @ORM\Column(type="boolean") */
    protected $IsNotForSite = false;

    /** @ORM\Column(type="boolean") */
    protected $NewForCurrentEdition = false;

    /** @ORM\Column(length=10) */
    protected $CountryEditionCode = 'RUS';

    /** @ORM\Column(type="boolean") */
    protected $IsApproved = true;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $CountOfColorPhoto;

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

    /** @ORM\Column(type="text", nullable=true) */
    protected $PharmDelivery;

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
     * @ORM\ManyToMany(targetEntity="ATC", inversedBy="documents")
     * @ORM\JoinTable(name="documentoc_atc",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ATCCode", referencedColumnName="ATCCode")})
     */
    protected $atcCodes;

    /**
     * @ORM\ManyToMany(targetEntity="Nozology", inversedBy="documents", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="document_indicnozology",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")})
     */
    protected $nozologies;

    /**
     * @ORM\ManyToMany(targetEntity="ClinicoPhPointers", inversedBy="documents", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="document_clphpointers",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ClPhPointerID", referencedColumnName="ClPhPointerID")})
     */
    protected $clphPointers;

    /**
     * @ORM\ManyToMany(targetEntity="InfoPage", inversedBy="documents")
     * @ORM\JoinTable(name="document_infopage",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")})
     */
    protected $infoPages;

    /**
     * @ORM\ManyToMany(targetEntity="Molecule", inversedBy="documents")
     * @ORM\JoinTable(name="molecule_document",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="MoleculeID", referencedColumnName="MoleculeID")})
     */
    protected $molecules;

    /**
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="documents")
     * @ORM\JoinTable(name="article_document",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")})
     */
    protected $articles;

    /**
     * @ORM\ManyToMany(targetEntity="Art", mappedBy="documents")
     * @ORM\JoinTable(name="art_document",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="art_id", referencedColumnName="id")})
     */
    protected $arts;

    /**
     * @ORM\ManyToMany(targetEntity="Publication", mappedBy="documents")
     * @ORM\JoinTable(name="publication_document",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")})
     */
    protected $publications;

    /**
     * @ORM\ManyToMany(targetEntity="PharmArticle", mappedBy="documents")
     * @ORM\JoinTable(name="pharm_article_document",
     *        joinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="pharm_article_id", referencedColumnName="id")})
     */
    protected $pharmArticles;

    /** @ORM\OneToMany(targetEntity="PharmPortfolio", mappedBy="DocumentID", fetch="EXTRA_LAZY") */
    protected $portfolios;

    /** @ORM\OneToMany(targetEntity="Product", mappedBy="document") */
    protected $products;

    /** @ORM\OneToMany(targetEntity="Product", mappedBy="document_all") */
    protected $products_all;

    /** @ORM\OneToMany(targetEntity="Product", mappedBy="document_merge") */
    protected $products_merge;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $GenDocsRouteID;

    /** @ORM\Column(type="string", nullable=true) */
    protected $ApproveCode;

    /** @ORM\Column(type="text", nullable=true) */
    protected $LactationInsuf;

    /** @ORM\Column(type="text", nullable=true) */
    protected $PregnancyInsuf;

    /** @ORM\Column(type="boolean") */
    protected $inactive = false;

    public function __construct()
    {
        $this->atcCodes = new ArrayCollection();
        $this->nozologies = new ArrayCollection();
        $this->molecules = new ArrayCollection();
        $this->clphPointers = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->arts = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->pharmArticles = new ArrayCollection();
        $this->portfolios = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->products_all = new ArrayCollection();
        $this->products_merge = new ArrayCollection();
        $this->infoPages = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getDocumentID() . (empty($this->RusName) ? '' : ' - ' . $this->RusName);
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
     * @param mixed $ChildInsuf
     */
    public function setChildInsuf($ChildInsuf)
    {
        $this->ChildInsuf = $ChildInsuf;
    }

    /**
     * @return mixed
     */
    public function getChildInsuf()
    {
        return $this->ChildInsuf;
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
    public function getChildInsufUsing()
    {
        return $this->ChildInsufUsing;
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
     * @param mixed $ElderlyInsuf
     */
    public function setElderlyInsuf($ElderlyInsuf)
    {
        $this->ElderlyInsuf = $ElderlyInsuf;
    }

    /**
     * @return mixed
     */
    public function getElderlyInsuf()
    {
        return $this->ElderlyInsuf;
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
    public function getElderlyInsufUsing()
    {
        return $this->ElderlyInsufUsing;
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
     * @param mixed $HepatoInsuf
     */
    public function setHepatoInsuf($HepatoInsuf)
    {
        $this->HepatoInsuf = $HepatoInsuf;
    }

    /**
     * @return mixed
     */
    public function getHepatoInsuf()
    {
        return $this->HepatoInsuf;
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
    public function getHepatoInsufUsing()
    {
        return $this->HepatoInsufUsing;
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
     * @param mixed $Interaction
     */
    public function setInteraction($Interaction)
    {
        $this->Interaction = $Interaction;
    }

    /**
     * @return mixed
     */
    public function getInteraction()
    {
        return $this->Interaction;
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
     * @param mixed $Lactation
     */
    public function setLactation($Lactation)
    {
        $this->Lactation = $Lactation;
    }

    /**
     * @return mixed
     */
    public function getLactation()
    {
        return $this->Lactation;
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
     * @param mixed $OverDosage
     */
    public function setOverDosage($OverDosage)
    {
        $this->OverDosage = $OverDosage;
    }

    /**
     * @return mixed
     */
    public function getOverDosage()
    {
        return $this->OverDosage;
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
     * @param mixed $PhKinetics
     */
    public function setPhKinetics($PhKinetics)
    {
        $this->PhKinetics = $PhKinetics;
    }

    /**
     * @return mixed
     */
    public function getPhKinetics()
    {
        return $this->PhKinetics;
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
     * @param mixed $PregnancyUsing
     */
    public function setPregnancyUsing($PregnancyUsing)
    {
        $this->PregnancyUsing = $PregnancyUsing;
    }

    /**
     * @return mixed
     */
    public function getPregnancyUsing()
    {
        return $this->PregnancyUsing;
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
    public function getRenalInsuf()
    {
        return $this->RenalInsuf;
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
    public function getRenalInsufUsing()
    {
        return $this->RenalInsufUsing;
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
    public function getRusNameWithElaboration()
    {
        if($this->getElaboration()) {
            return $this->RusName." ".$this->getElaboration();
        }

        return $this->RusName;
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
     * @param mixed $WithoutHepatoInsuf
     */
    public function setWithoutHepatoInsuf($WithoutHepatoInsuf)
    {
        $this->WithoutHepatoInsuf = $WithoutHepatoInsuf;
    }

    /**
     * @return mixed
     */
    public function getWithoutHepatoInsuf()
    {
        return $this->WithoutHepatoInsuf;
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
    public function getWithoutRenalInsuf()
    {
        return $this->WithoutRenalInsuf;
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
     * @param mixed $nozologies
     */
    public function setNozologies(ArrayCollection $nozologies)
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
     * @param mixed $NursingUsing
     */
    public function setNursingUsing($NursingUsing)
    {
        $this->NursingUsing = $NursingUsing;
    }

    /**
     * @return mixed
     */
    public function getNursingUsing()
    {
        return $this->NursingUsing;
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
     * @param mixed $portfolios
     */
    public function setPortfolios($portfolios)
    {
        $this->portfolios = $portfolios;
    }

    /**
     * @return mixed
     */
    public function getPortfolios()
    {
        return $this->portfolios;
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

    public function isBAD()
    {
        return $this->ArticleID == 6;
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
    public function getGenDocsRouteID()
    {
        return $this->GenDocsRouteID;
    }

    /**
     * @param mixed $GenDocsRouteID
     */
    public function setGenDocsRouteID($GenDocsRouteID)
    {
        $this->GenDocsRouteID = $GenDocsRouteID;
    }

    public function getId()
    {
        return $this->DocumentID;
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

    public function removeClPhPointers($g)
    {
        $this->clphPointers->removeElement($g);
    }

    public function addClPhPointers($g)
    {
        $this->clphPointers[] = $g;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApproveCode()
    {
        return $this->ApproveCode;
    }

    /**
     * @param mixed $ApproveCode
     */
    public function setApproveCode($ApproveCode)
    {
        $this->ApproveCode = $ApproveCode;
    }

    /**
     * @return mixed
     */
    public function getLactationInsuf()
    {
        return $this->LactationInsuf;
    }

    /**
     * @param mixed $LactationInsuf
     */
    public function setLactationInsuf($LactationInsuf)
    {
        $this->LactationInsuf = $LactationInsuf;
    }

    /**
     * @return mixed
     */
    public function getPregnancyInsuf()
    {
        return $this->PregnancyInsuf;
    }

    /**
     * @param mixed $PregnancyInsuf
     */
    public function setPregnancyInsuf($PregnancyInsuf)
    {
        $this->PregnancyInsuf = $PregnancyInsuf;
    }

    /**
     * @return mixed
     */
    public function getProductsAll()
    {
        return $this->products_all;
    }

    /**
     * @param mixed $products_all
     */
    public function setProductsAll($products_all)
    {
        $this->products_all = $products_all;
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
    public function getProductsMerge()
    {
        return $this->products_merge;
    }

    /**
     * @param mixed $products_merge
     */
    public function setProductsMerge($products_merge)
    {
        $this->products_merge = $products_merge;
    }
}