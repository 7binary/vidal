<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="marketstatus") */
class MarketStatus
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $MarketStatusID;

	/** @ORM\Column(length=80) */
	protected $RusName;

	/** @ORM\Column(length=80) */
	protected $EngName;

	/** @ORM\OneToMany(targetEntity="Product", mappedBy="MarketStatusID") */
	protected $products;

	/** @ORM\OneToMany(targetEntity="Molecule", mappedBy="MarketStatusID") */
	protected $molecules;

	public function __construct()
	{
		$this->products  = new ArrayCollection();
		$this->molecules = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->RusName;
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
	 * @param mixed $products
	 */
	public function setProducts(ArrayCollection $products)
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
	 * @param mixed $molecules
	 */
	public function setMolecules(ArrayCollection $molecules)
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
}