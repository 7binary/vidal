<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="producttype") */
class ProductType
{
	/** @ORM\Id @ORM\Column(length=4, unique=true) */
	protected $ProductTypeCode;

	/** @ORM\Column(length=255, nullable=true) */
	protected $RusName;

	/** @ORM\Column(length=255, nullable=true) */
	protected $EngName;

	public function __toString()
	{
		return $this->RusName;
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
	 * @param mixed $ProductTypeCode
	 */
	public function setProductTypeCode($ProductTypeCode)
	{
		$this->ProductTypeCode = $ProductTypeCode;
	}

	/**
	 * @return mixed
	 */
	public function getProductTypeCode()
	{
		return $this->ProductTypeCode;
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
}