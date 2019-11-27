<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="MoleculeBaseRepository")
 * @ORM\Table(name="moleculebase")
 */
class MoleculeBase
{
	/** @ORM\Id @ORM\Column(length=20, unique=true) */
	protected $GNParent;

	/** @ORM\Column(length=255) */
	protected $description;

	/** @ORM\OneToMany(targetEntity="Molecule", mappedBy="GNParent") */
	protected $molecules;

	public function __construct()
	{
		$this->molecules = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->GNParent;
	}

	/**
	 * @param mixed $GNParent
	 */
	public function setGNParent($GNParent)
	{
		$this->GNParent = $GNParent;
	}

	/**
	 * @return mixed
	 */
	public function getGNParent()
	{
		return $this->GNParent;
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
	public function getDescription()
	{
		return $this->description;
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