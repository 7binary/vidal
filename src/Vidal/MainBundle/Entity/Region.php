<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="RegionRepository") @ORM\Table(name="region") */
class Region
{
	/** @ORM\Id @ORM\Column(type = "integer") @ORM\GeneratedValue */
	protected $id;

	/** @ORM\OneToMany(targetEntity="City", mappedBy="region") */
	protected $cities;

	/** @ORM\ManyToOne(targetEntity = "Country", inversedBy="regions") */
	protected $country;

	/**
	 * @ORM\Column(type="string", length=63)
	 * @Assert\NotBlank(message="Укажите название страны.")
	 * @Assert\Length(max=63, maxMessage="Название страны должно быть не длиннее 63 знаков.")
	 */
	protected $title;

	/** @ORM\OneToMany(targetEntity="User", mappedBy="region") */
	protected $doctors;

    /** @ORM\ManyToMany(targetEntity="Digest", mappedBy="specialties") */
    protected $digests;

	public function __construct()
	{
		$this->cities  = new ArrayCollection();
		$this->doctors = new ArrayCollection();
        $this->digests = new ArrayCollection();
	}

	public function __toString()
	{
		$country = $this->getCountry();

		return $this->title . ', ' . $country;
	}

	/**
	 * @param mixed $cities
	 */
	public function setCities($cities)
	{
		$this->cities = $cities;
	}

	/**
	 * @return mixed
	 */
	public function getCities()
	{
		return $this->cities;
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
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
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
}