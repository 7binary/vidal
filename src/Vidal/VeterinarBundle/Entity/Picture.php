<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="PictureRepository") @ORM\Table(name="picture") */
class Picture
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $PictureID;

	/** @ORM\Column(length=255, nullable=true) */
	protected $PathForBookEdition;

	/** @ORM\Column(length=255) */
	protected $PathForElectronicEdition;

	/**
	 * @ORM\ManyToMany(targetEntity="InfoPage", inversedBy="pictures")
	 * @ORM\JoinTable(name="infopage_picture",
	 *        joinColumns={@ORM\JoinColumn(name="PictureID", referencedColumnName="PictureID")},
	 *        inverseJoinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")})
	 */
	protected $infoPages;

	public function __construct()
	{
		$this->infoPages       = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->PathForElectronicEdition;
	}

	/**
	 * @param mixed $PathForBookEdition
	 */
	public function setPathForBookEdition($PathForBookEdition)
	{
		$this->PathForBookEdition = $PathForBookEdition;
	}

	/**
	 * @return mixed
	 */
	public function getPathForBookEdition()
	{
		return $this->PathForBookEdition;
	}

	/**
	 * @param mixed $PathForElectronicEdition
	 */
	public function setPathForElectronicEdition($PathForElectronicEdition)
	{
		$this->PathForElectronicEdition = $PathForElectronicEdition;
	}

	/**
	 * @return mixed
	 */
	public function getPathForElectronicEdition()
	{
		return $this->PathForElectronicEdition;
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
	 * @param mixed $infoPages
	 */
	public function setInfoPages(ArrayCollection $infoPages)
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
}