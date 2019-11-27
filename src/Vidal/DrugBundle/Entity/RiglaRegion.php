<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity() @ORM\Table(name="rigla_region") */
class RiglaRegion
{
    /** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
    protected $id;

    /** @ORM\Column(length=255) */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="RiglaPrice", mappedBy="regionId")
     */
    protected $prices;

    public function __construct()
    {
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
    public function getName()
    {
        return $this->name;
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
}