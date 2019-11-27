<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="moleculename") */
class MoleculeName
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $MoleculeNameID;

	/**
	 * @ORM\ManyToOne(targetEntity="Molecule", inversedBy="moleculeNames")
	 * @ORM\JoinColumn(name="MoleculeID", referencedColumnName="MoleculeID")
	 */
	protected $MoleculeID;

	/** @ORM\Column(type="text") */
	protected $RusName;

	/** @ORM\Column(type="text", nullable=true) */
	protected $EngName;

	/** @ORM\Column(type="integer", name="GDDB_MoleculeID", nullable=true) */
	protected $gddbMoleculeID;

	/**
	 * @ORM\ManyToMany(targetEntity="Product", mappedBy="moleculeNames")
	 * @ORM\JoinTable(name="product_moleculename",
	 *        joinColumns={@ORM\JoinColumn(name="MoleculeNameID", referencedColumnName="MoleculeNameID")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")})
	 */
	protected $products;

	public function __construct()
	{
		$this->products       = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->MoleculeNameID . ' - ' . $this->RusName;
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
	 * @param mixed $MoleculeID
	 */
	public function setMoleculeID($MoleculeID)
	{
		$this->MoleculeID = $MoleculeID;
	}

	/**
	 * @return mixed
	 */
	public function getMoleculeID()
	{
		return $this->MoleculeID;
	}

	/**
	 * @param mixed $MoleculeNameID
	 */
	public function setMoleculeNameID($MoleculeNameID)
	{
		$this->MoleculeNameID = $MoleculeNameID;
	}

	/**
	 * @return mixed
	 */
	public function getMoleculeNameID()
	{
		return $this->MoleculeNameID;
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
	 * @param mixed $gddbMoleculeID
	 */
	public function setGddbMoleculeID($gddbMoleculeID)
	{
		$this->gddbMoleculeID = $gddbMoleculeID;
	}

	/**
	 * @return mixed
	 */
	public function getGddbMoleculeID()
	{
		return $this->gddbMoleculeID;
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
}