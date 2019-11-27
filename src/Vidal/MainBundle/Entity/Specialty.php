<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="SpecialtyRepository") @ORM\Table(name="specialty") */
class Specialty
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank(message="Укажите название специальности.")
	 * @Assert\Length(max=127, maxMessage="Название специальности не может быть длиннее {{limit}} знаков.")
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank(message="Укажите сокращенное название специальности.")
	 * @Assert\Length(max=127, maxMessage="Cокращенное название специальности специальности не может быть длиннее {{limit}} знаков.")
	 */
	protected $shortName;

	/**
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank(message="Укажите как назвается врач данной специальности.")
	 * @Assert\Length(max=127, maxMessage="Название врача специальности не может быть длиннее {{limit}} знаков.")
	 */
	protected $doctorName;

	/** @ORM\OneToMany(targetEntity="User", mappedBy="primarySpecialty") */
	protected $primarySpecialties;

	/** @ORM\OneToMany(targetEntity="User", mappedBy="secondarySpecialty") */
	protected $secondarySpecialties;

	/** @ORM\ManyToMany(targetEntity="Digest", mappedBy="specialties") */
	protected $digests;

	/** @ORM\ManyToMany(targetEntity="Digest", mappedBy="specialties") */
	protected $deliveries;

	public function __construct()
	{
		$this->primarySpecialties   = new ArrayCollection();
		$this->secondarySpecialties = new ArrayCollection();
		$this->digests              = new ArrayCollection();
		$this->deliveries           = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->title;
	}

	/**
	 * @param mixed $doctorName
	 */
	public function setDoctorName($doctorName)
	{
		$this->doctorName = $doctorName;
	}

	/**
	 * @return mixed
	 */
	public function getDoctorName()
	{
		return $this->doctorName;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $shortName
	 */
	public function setShortName($shortName)
	{
		$this->shortName = $shortName;
	}

	/**
	 * @return mixed
	 */
	public function getShortName()
	{
		return $this->shortName;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param mixed $primarySpecialties
	 */
	public function setPrimarySpecialties($primarySpecialties)
	{
		$this->primarySpecialties = $primarySpecialties;
	}

	/**
	 * @return mixed
	 */
	public function getPrimarySpecialties()
	{
		return $this->primarySpecialties;
	}

	public function getPrimaryDoctors()
	{
		return $this->primarySpecialties;
	}

	/**
	 * @param mixed $secondarySpecialties
	 */
	public function setSecondarySpecialties($secondarySpecialties)
	{
		$this->secondarySpecialties = $secondarySpecialties;
	}

	/**
	 * @return mixed
	 */
	public function getSecondarySpecialties()
	{
		return $this->secondarySpecialties;
	}

	public function getSecondaryDoctors()
	{
		return $this->secondarySpecialties;
	}

	/**
	 * @return mixed
	 */
	public function getDigests()
	{
		return $this->digests;
	}

	/**
	 * @param mixed $digests
	 */
	public function setDigests($digests)
	{
		$this->digests = $digests;
	}

	/**
	 * @return mixed
	 */
	public function getDeliveries()
	{
		return $this->deliveries;
	}

	/**
	 * @param mixed $deliveries
	 */
	public function setDeliveries($deliveries)
	{
		$this->deliveries = $deliveries;
	}
}