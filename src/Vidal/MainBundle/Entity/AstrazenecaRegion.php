<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table()
 */
class AstrazenecaRegion
{

    /**
     * @ORM\Id
     * @ORM\Column(type = "integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type = "string")
     */
    protected $latitude;

    /**
     * @ORM\Column(type = "string")
     */
    protected $longitude;

    /**
     * @ORM\Column(type = "integer")
     */
    protected $zoom;

    /**
     * @ORM\Column(type = "string")
     */
    protected $title;

    /**
     * @ORM\OneToMany(targetEntity="AstrazenecaMap", mappedBy="region")
     */
    protected $coords;

    public function __construct(){
        $this->coords = new ArrayCollection();
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
    public function getId()
    {
        return $this->id;
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
     * @param mixed $coords
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;
    }

    /**
     * @return mixed
     */
    public function getCoords()
    {
        return $this->coords;
    }


    public function addCoord($coord){
        $this->coords[] = $coord;
    }

    public function removeCoord($coord){
        $this->coords->removeElement($coord);
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param mixed $zoom
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;
    }

    /**
     * @return mixed
     */
    public function getZoom()
    {
        return $this->zoom;
    }



}