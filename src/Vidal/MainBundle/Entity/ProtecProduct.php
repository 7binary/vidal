<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="ProtecProductRepository") @ORM\Table(name="protec_product")
 */
class ProtecProduct extends BaseEntity
{
    /**
     * @ORM\Id @ORM\Column(type = "integer")
     */
    protected $id;

    /** @ORM\Column(length=500) */
    protected $title;

    /** @ORM\Column(length=500) */
    protected $form;

    /** @ORM\OneToMany(targetEntity="ProtecPrice", mappedBy="product") */
    protected $prices;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $ProductID;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $clicked = 0;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
        $this->enabled = true;
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
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form)
    {
        $this->form = $form;
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
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param mixed $ProductID
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
    }

    /**
     * @return mixed
     */
    public function getClicked()
    {
        return $this->clicked;
    }

    /**
     * @param mixed $clicked
     */
    public function setClicked($clicked)
    {
        $this->clicked = $clicked;
    }
}