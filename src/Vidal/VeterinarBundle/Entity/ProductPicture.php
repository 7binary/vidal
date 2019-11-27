<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="productpicture") */
class ProductPicture
{
	/** @ORM\Id @ORM\Column(type="integer") */
	protected $ProductID;

	/** @ORM\Id @ORM\Column(type="integer", nullable=true) */
	protected $PictureID;

	/** @ORM\Column(length=10, nullable=true) */
	protected $YearEdition;

	/** @ORM\Column(length=4, nullable=true) */
	protected $CountryEditionCode;

	/** @ORM\Column(length=4, nullable=true) */
	protected $EditionCode;

    /** @ORM\Column(length=50, nullable=true) */
    protected $DateEdit;

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
	 * @param mixed $EditionCode
	 */
	public function setEditionCode($EditionCode)
	{
		$this->EditionCode = $EditionCode;
	}

	/**
	 * @return mixed
	 */
	public function getEditionCode()
	{
		return $this->EditionCode;
	}

	/**
	 * @param mixed $PictureID
	 */
	public function setPictureID($PictureID)
	{
		$this->PictureID = $PictureID;
	}

	/**
	 * @return mixed
	 */
	public function getPictureID()
	{
		return $this->PictureID;
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
     * @return mixed
     */
    public function getDateEdit()
    {
        return $this->DateEdit;
    }

    /**
     * @param mixed $DateEdit
     */
    public function setDateEdit($DateEdit)
    {
        $this->DateEdit = $DateEdit;
    }
}