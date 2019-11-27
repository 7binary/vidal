<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity() @ORM\Table(name="rigla_price") */
class RiglaPrice
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\ManyToOne(targetEntity="RiglaRegion", inversedBy="prices") */
	protected $region;

    /** @ORM\ManyToOne(targetEntity="Rigla", inversedBy="prices") */
    protected $rigla;

    /** @ORM\Column(length=255) */
    protected $price;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
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
    public function getRigla()
    {
        return $this->rigla;
    }

    /**
     * @param mixed $rigla
     */
    public function setRigla($rigla)
    {
        $this->rigla = $rigla;
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
}