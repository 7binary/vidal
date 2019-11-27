<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="ProtecRegionRepository") @ORM\Table(name="protec_region") */
class ProtecRegion
{
	/** @ORM\Column(type="integer") @ORM\Id */
	protected $id;

    /** @ORM\Column(length=255) */
    protected $title;

    /** @ORM\Column(length=255, nullable=true) */
    protected $title2;

    /** @ORM\Column(length=255, nullable=true) */
    protected $title3;

    /** @ORM\OneToMany(targetEntity="ProtecPrice", mappedBy="region") */
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
    public function getTitle2()
    {
        return $this->title2;
    }

    /**
     * @param mixed $title2
     */
    public function setTitle2($title2)
    {
        $this->title2 = $title2;
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
    public function getTitle3()
    {
        return $this->title3;
    }

    /**
     * @param mixed $title3
     */
    public function setTitle3($title3)
    {
        $this->title3 = $title3;
    }
}