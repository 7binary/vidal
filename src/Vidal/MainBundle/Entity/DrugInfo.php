<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity(repositoryClass="DrugInfoRepository") @ORM\Table(name="drug_info") */
class DrugInfo extends BaseEntity
{
    /** @ORM\Column(length=20) */
    protected $entityClass;

    /** @ORM\Column(type="integer") */
    protected $entityId;

    /** @ORM\Column(length=255, nullable=true) */
    protected $uri;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $ga_pageviews;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $ga_from;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $ga_to;

    /**
     * @return mixed
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param mixed $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return mixed
     */
    public function getGaPageviews()
    {
        return $this->ga_pageviews;
    }

    /**
     * @param mixed $ga_pageviews
     */
    public function setGaPageviews($ga_pageviews)
    {
        $this->ga_pageviews = $ga_pageviews;
    }

    /**
     * @return mixed
     */
    public function getGaFrom()
    {
        return $this->ga_from;
    }

    /**
     * @param mixed $ga_from
     */
    public function setGaFrom($ga_from)
    {
        $this->ga_from = $ga_from;
    }

    /**
     * @return mixed
     */
    public function getGaTo()
    {
        return $this->ga_to;
    }

    /**
     * @param mixed $ga_to
     */
    public function setGaTo($ga_to)
    {
        $this->ga_to = $ga_to;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param mixed $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }
}