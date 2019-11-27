<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="RiglaRepository") @ORM\Table(name="rigla") */
class Rigla
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(type="integer") */
	protected $code;

	/** @ORM\Column(type="boolean") */
	protected $inactive = false;

    /**
     * @ORM\OneToMany(targetEntity="RiglaPrice", mappedBy="rigla")
     */
    protected $prices;

    /**
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="riglas")
     * @ORM\JoinTable(name="rigla_product",
     *        joinColumns={@ORM\JoinColumn(name="rigla_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")})
     */
    protected $products;

    /** @ORM\Column(length=255, nullable=true) */
    protected $title;

    /** @ORM\Column(length=255, nullable=true) */
    protected $firm;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->prices = new ArrayCollection();
    }

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * @param mixed $inactive
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
    }

    /**
     * @return mixed
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param mixed $prices
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->products;
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
    public function getTitle()
    {
        return $this->title;
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
    public function getFirm()
    {
        return $this->firm;
    }

    /**
     * @param mixed $firm
     */
    public function setFirm($firm)
    {
        $this->firm = $firm;
    }
}