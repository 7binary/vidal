<?php
namespace Vidal\MainBundle\Market;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Bundle\TwigBundle\TwigEngine as Templating;

class Product{

    protected $title;

    protected $manufacturer;

    protected $price;

    protected $code;

    protected $count;

    // Группировка по названию аптеки
    protected $groupApt;

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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $groupApt
     */
    public function setGroupApt($groupApt)
    {
        $this->groupApt = $groupApt;
    }

    /**
     * @return mixed
     */
    public function getGroupApt()
    {
        return $this->groupApt;
    }



    /**
     * @param mixed $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return mixed
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
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
    public function getTitle()
    {
        return $this->title;
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
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    public function addCount($count){
        $this->count += $count;
    }
}