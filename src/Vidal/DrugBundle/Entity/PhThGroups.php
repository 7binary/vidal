<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="PhThGroupsRepository") @ORM\Table(name="phthgroups") */
class PhThGroups
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=500) */
	protected $Name;

	/** 
	 * @ORM\ManyToMany(targetEntity="Product", mappedBy="phthgroups")
	 * @ORM\JoinTable(name="product_phthgrp",
	 * 		joinColumns={@ORM\JoinColumn(name="PhThGroupsId", referencedColumnName="id")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")})
	 */
	protected $products;

	public function __construct()
	{
		$this->products = new ArrayCollection();
	}

	public function __toString()
	{
		return $this->id . ' - ' . $this->Name;
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
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->Name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->Name;
	}

	/**
	 * @param mixed $products
	 */
	public function setProducts($products)
	{
		$this->products = $products;
	}

	/**
	 * @return mixed
	 */
	public function getProducts()
	{
		return $this->products;
	}
}