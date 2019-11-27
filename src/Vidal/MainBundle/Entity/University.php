<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity() @ORM\Table(name="university") */
class University 
{
	/** @ORM\Id @ORM\Column(type = "integer") @ORM\GeneratedValue */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Country", inversedBy="universities")
	 * @Assert\NotBlank(message="Пожалуйста, укажите страну ВУЗа.")
	 */
	protected $country;
	
	/** @ORM\OneToMany(targetEntity="User", mappedBy="university") */
	protected $doctors;
	
	/**
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank(message = "Пожалуйста, укажите название ВУЗа.")
	 */
	protected $title;

	public function __contsruct()
	{
		$this->doctors = new ArrayCollection();
	}
	
	public function __toString()
	{
		return $this->title;
	}
	
	public function getId()
	{
		return $this->id;
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
	
	public function getCountry()
	{
		return $this->country;
	}
	
	public function setCountry(Country $country)
	{
		$this->country = $country;
		
		return $this;
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
}