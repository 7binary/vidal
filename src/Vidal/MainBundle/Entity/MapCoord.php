<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="MapCoordRepository")
 * @ORM\Table(name="mapcoords")
 */
class MapCoord
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type = "integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type = "string")
	 */
	protected $title;

	/**
	 * @ORM\Column(type = "text")
	 */
	protected $text = '';

	/**
	 * @ORM\Column(type = "integer")
	 */
	protected $offerId;

	/**
	 * @ORM\Column(type = "string")
	 */
	protected $latitude;

	/**
	 * @ORM\Column(type = "string")
	 */
	protected $longitude;

	/**
	 * @ORM\ManyToOne(targetEntity="MapRegion", inversedBy="coords")
	 */
	protected $region;

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
	 * @param mixed $latitude
	 */
	public function setLatitude($latitude)
	{
		$this->latitude = $latitude;
	}

	/**
	 * @return mixed
	 */
	public function getLatitude()
	{
		return $this->latitude;
	}

	/**
	 * @param mixed $longitude
	 */
	public function setLongitude($longitude)
	{
		$this->longitude = $longitude;
	}

	/**
	 * @return mixed
	 */
	public function getLongitude()
	{
		return $this->longitude;
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
	 * @param mixed $offerId
	 */
	public function setOfferId($offerId)
	{
		$this->offerId = $offerId;
	}

	/**
	 * @return mixed
	 */
	public function getOfferId()
	{
		return $this->offerId;
	}

	/**
	 * @param mixed $text
	 */
	public function setText($text = '')
	{
		$this->text = $text;
	}

	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->text;
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
}