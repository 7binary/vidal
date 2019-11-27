<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="product_item_route") */
class ProductItemRoute
{
    /** @ORM\Id @ORM\Column(type="integer") */
    protected $ProductID;

    /** @ORM\Id @ORM\Column(type="integer") */
    protected $ItemID;

    /** @ORM\Id @ORM\Column(type="integer") */
    protected $RouteID;

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
    public function getRouteID()
    {
        return $this->RouteID;
    }

    /**
     * @param mixed $RouteID
     */
    public function setRouteID($RouteID)
    {
        $this->RouteID = $RouteID;
    }
}