<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="CityRepository") @ORM\Table(name="city") */
class City
{
	/** @ORM\Id @ORM\Column(type = "integer") @ORM\GeneratedValue */
	protected $id;

	/**
	 * @ORM\Column(type ="string")
	 * @Assert\NotBlank(message="Пожалуйста, укажите название города.")
	 * @Assert\Length(max=63, maxMessage="Название города не может быть длиннее {{limit}}.")
	 */
	protected $title;

	/**
	 * @ORM\ManyToOne(targetEntity="Country", inversedBy="cities")
	 * @Assert\NotBlank(message="Пожалуйста, укажите страну.")
	 */
	protected $country;

	/**
	 * @ORM\ManyToOne(targetEntity="Region", inversedBy="cities")
	 * @Assert\NotBlank(message="Пожалуйста, укажите регион.")
	 */
	protected $region;

	/** @ORM\OneToMany(targetEntity="User", mappedBy="city") */
	protected $doctors;

	/** @ORM\OneToMany(targetEntity="QuestionAnswer", mappedBy="city") */
	protected $qa;

	public function __construct()
	{
		$this->doctors = new ArrayCollection();
		$this->qa      = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->title;
	}

	/**
	 * @param mixed $country
	 */
	public function setCountry($country)
	{
		$this->country = $country;
	}

	/**
	 * @return mixed
	 */
	public function getCountry()
	{
		return $this->country;
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
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $region
	 */
	public function setRegion($region)
	{
		$this->region = $region;
	}

	/**
	 * @return mixed
	 */
	public function getRegion()
	{
		return $this->region;
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
	 * @return mixed
	 */
	public function getQa()
	{
		return $this->qa;
	}

	/**
	 * @param mixed $qa
	 */
	public function setQa($qa)
	{
		$this->qa = $qa;
	}
}