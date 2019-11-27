<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="bannergroup")
 */
class BannerGroup
{
    const BOTTOM = 1;
    const TOP = 2;
    const LEFT = 7;
    const RIGHT = 10;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(length=100)
	 * @Assert\NotBlank(message = "Укажите название группы баннеров")
	 */
	protected $title;

	/**
	 * @ORM\OneToMany(targetEntity="Banner", mappedBy="group")
	 */
	protected $banners;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $enabled;

	/** @ORM\Column(type="integer") */
	protected $width;

	/** @ORM\Column(type="integer") */
	protected $height;

	/** @ORM\Column(length=50, nullable=true) */
	protected $name;

	public function __construct()
	{
		$this->banners = new ArrayCollection();
		$this->enabled = true;
	}

	public function __toString()
	{
		return empty($this->title) ? '' : $this->title;
	}

	/**
	 * @param mixed $banners
	 */
	public function setBanners(ArrayCollection $banners)
	{
		$this->banners = $banners;
	}

	/**
	 * @return mixed
	 */
	public function getBanners()
	{
		return $this->banners;
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
	 * @param mixed $enabled
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @return mixed
	 */
	public function getEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @param mixed $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @return mixed
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param mixed $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return mixed
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}
}