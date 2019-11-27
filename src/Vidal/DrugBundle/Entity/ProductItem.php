<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="product_item") */
class ProductItem
{
    /** @ORM\Id @ORM\Column(type="integer") */
    protected $ProductID;

    /** @ORM\Id @ORM\Column(type="integer") */
    protected $ItemID;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $Ranking;

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
    public function getItemID()
    {
        return $this->ItemID;
    }

    /**
     * @param mixed $ItemID
     */
    public function setItemID($ItemID)
    {
        $this->ItemID = $ItemID;
    }

    /**
     * @return mixed
     */
    public function getRanking()
    {
        return $this->Ranking;
    }

    /**
     * @param mixed $Ranking
     */
    public function setRanking($Ranking)
    {
        $this->Ranking = $Ranking;
    }

    public function getId()
    {
        return $this->ProductID . '+' . $this->ItemID;
    }
}