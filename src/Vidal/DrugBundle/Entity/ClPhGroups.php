<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="ClPhGroupsRepository") @ORM\Table(name="clphgroups") */
class ClPhGroups
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    protected $ClPhGroupsID;

    /** @ORM\Column(length=255) */
    protected $Name;

    /** @ORM\Column(length=50, nullable=true) */
    protected $Code;

    /**
     * @ORM\ManyToMany(targetEntity="Product", mappedBy="clphGroups")
     * @ORM\JoinTable(name="product_clphgroups",
     *        joinColumns={@ORM\JoinColumn(name="ClPhGroupsID", referencedColumnName="ClPhGroupsID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")})
     */
    protected $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->Code . ' - ' . $this->Name;
    }

    /**
     * @param mixed $ClPhGroupsID
     */
    public function setClPhGroupsID($ClPhGroupsID)
    {
        $this->ClPhGroupsID = $ClPhGroupsID;
    }

    /**
     * @return mixed
     */
    public function getClPhGroupsID()
    {
        return $this->ClPhGroupsID;
    }

    /**
     * @param mixed $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
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

    public function getId()
    {
        return $this->ClPhGroupsID;
    }
}