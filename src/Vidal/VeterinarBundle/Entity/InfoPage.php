<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="InfoPageRepository") @ORM\Table(name="infopage") @FileStore\Uploadable */
class InfoPage
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $InfoPageID;

	/** @ORM\Column(length=255, nullable=true) */
	protected $Name;

	/** @ORM\Column(length=255, unique=true) */
	protected $RusName;

	/** @ORM\Column(length=255, nullable=true) */
	protected $EngName;

	/** @ORM\Column(type="text", nullable=true) */
	protected $RusAddress;

	/** @ORM\Column(type="text", nullable=true) */
	protected $EngAddress;

	/** @ORM\Column(type="text", nullable=true) */
	protected $ShortAddress;

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

	/** @ORM\Column(type="datetime") @Gedmo\Timestampable(on="update") */
	protected $DateTextModified;

	/**
	 * @ORM\Column(length=10)
	 */
	protected $CountryEditionCode = 'RUS';

	/** @ORM\ManyToMany(targetEntity="Picture", mappedBy="infoPages") */
	protected $pictures;

	/**
	 * @ORM\ManyToMany(targetEntity="Document", mappedBy="infoPages")
	 * @ORM\JoinTable(name="document_infopage",
	 *        joinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")})
	 */
	protected $documents;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @FileStore\UploadableField(mapping="infopage_photo")
	 */
	protected $photo;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $countProducts = 1;

	public function __construct()
	{
		$this->pictures  = new ArrayCollection();
		$this->documents = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->RusName;
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
	public function getCountryEditionCode()
	{
		return $this->CountryEditionCode;
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
	public function getDocuments()
	{
		return $this->documents;
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

    /**
     * @return mixed
     */
    public function getCountProducts()
    {
        return $this->countProducts;
    }

    /**
     * @param mixed $countProducts
     */
    public function setCountProducts($countProducts)
    {
        $this->countProducts = $countProducts;
    }
}