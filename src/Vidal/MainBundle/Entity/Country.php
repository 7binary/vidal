<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="CountryRepository") @ORM\Table(name="country") */
class Country
{
	const RUSSIA_COUNTRY_ID = 1;

	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $id;

	/** @ORM\OneToMany(targetEntity="City", mappedBy="country") */
	protected $cities;

	/** @ORM\OneToMany(targetEntity="Region", mappedBy="country") */
	protected $regions;

	/**
	 * @ORM\Column(type="string", length=63)
	 * @Assert\NotBlank(message="Укажите название страны.")
	 * @Assert\Length(max=63, maxMessage="Название страны должно быть не длиннее 63 знаков.")
	 */
	protected $title;

	/** @ORM\OneToMany(targetEntity="User", mappedBy="country") */
	protected $doctors;

    /**
     *  @ORM\Column(type="string", length=63)
     */
    protected $shortTitle;

	/** @ORM\OneToMany(targetEntity="University", mappedBy="country") */
	protected $universities;

	public function __construct()
	{
		$this->cities  = new ArrayCollection();
		$this->regions = new ArrayCollection();
		$this->doctors = new ArrayCollection();
		$this->universities = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->title;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getCities()
	{
		return $this->cities;
	}

	public function setCities(ArrayCollection $cities)
	{
		$this->cities = $cities;

		return $this;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @param mixed $regions
	 */
	public function setRegions($regions)
	{
		$this->regions = $regions;
	}

	/**
	 * @return mixed
	 */
	public function getRegions()
	{
		return $this->regions;
	}

	/**
	 * @param mixed $doctors
	 */
	public function setDoctors($doctors)
	{
		$this->doctors = $doctors;
	}

	/**
	 * @return mixed
	 */
	public function getDoctors()
	{
		return $this->doctors;
	}

	/**
	 * @param mixed $shortTitle
	 */
	public function setShortTitle($shortTitle)
	{
		$this->shortTitle = $shortTitle;
	}

	/**
	 * @return mixed
	 */
	public function getShortTitle()
	{
		return $this->shortTitle;
	}

	/**
	 * @param mixed $universities
	 */
	public function setUniversities($universities)
	{
		$this->universities = $universities;
	}

	/**
	 * @return mixed
	 */
	public function getUniversities()
	{
		return $this->universities;
	}
}