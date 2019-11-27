<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/** @ORM\Entity(repositoryClass="ProtecPriceRepository") @ORM\Table(name="protec_price") */
class ProtecPrice
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

    /** @ORM\ManyToOne(targetEntity="ProtecRegion", inversedBy="prices") */
    protected $region;

    /** @ORM\ManyToOne(targetEntity="ProtecProduct", inversedBy="prices") */
    protected $product;

    /** @ORM\Column(length=255, nullable=true) */
    protected $price;

    /** @ORM\Column(length=500) */
    protected $link;

    /**
     * @ORM\Column(type = "datetime", nullable=true)
     * @Gedmo\Timestampable(on = "create")
     */
    protected $created;

    /**
     * @ORM\Column(type = "datetime", nullable=true)
     * @Gedmo\Timestampable(on = "update")
     */
    protected $updated;

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
    public function getRegion()
    {
        return $this->region;
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }
}