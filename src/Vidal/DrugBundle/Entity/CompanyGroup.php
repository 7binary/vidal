<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity() @ORM\Table(name="companygroup") */
class CompanyGroup
{
    /** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
    protected $CompanyGroupID;

    /** @ORM\Column(length=255) */
    protected $RusName;

    /** @ORM\Column(length=255, nullable=true) */
    protected $EngName;

    /** @ORM\OneToMany(targetEntity="Company", mappedBy="CompanyGroupID") */
    protected $companies;

    public function __toString()
    {
        return empty($this->RusName) ? '' : $this->RusName;
    }

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCompanyGroupID()
    {
        return $this->CompanyGroupID;
    }

    /**
     * @param mixed $EngName
     */
    public function setEngName($EngName)
    {
        $this->EngName = $EngName;
    }

    /**
     * @return mixed
     */
    public function getEngName()
    {
        return $this->EngName;
    }

    /**
     * @param mixed $RusName
     */
    public function setRusName($RusName)
    {
        $this->RusName = $RusName;
    }

    /**
     * @return mixed
     */
    public function getRusName()
    {
        return $this->RusName;
    }

    /**
     * @param mixed $companies
     */
    public function setCompanies($companies)
    {
        $this->companies = $companies;
    }

    /**
     * @return mixed
     */
    public function getCompanies()
    {
        return $this->companies;
    }

    public function getId()
    {
        return $this->CompanyGroupID;
    }
}