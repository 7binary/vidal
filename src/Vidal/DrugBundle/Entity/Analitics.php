<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="AnaliticsRepository") @ORM\Table(name="analitics") */
class Analitics
{
    /** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Molecule", inversedBy="analitics")
     * @ORM\JoinTable(name="analitics_molecule",
     *        joinColumns={@ORM\JoinColumn(name="analitics_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="MoleculeID", referencedColumnName="MoleculeID")})
     */
    protected $molecules;

    /**
     * @ORM\ManyToMany(targetEntity="Vidal\DrugBundle\Entity\ATC", inversedBy="analitics")
     * @ORM\JoinTable(name="analitics_atc",
     *        joinColumns={@ORM\JoinColumn(name="analitics_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ATCCode", referencedColumnName="ATCCode")})
     */
    protected $atc;

    /**
     * @ORM\ManyToMany(targetEntity="Vidal\DrugBundle\Entity\InfoPage", inversedBy="analitics")
     * @ORM\JoinTable(name="analitics_infopages",
     *        joinColumns={@ORM\JoinColumn(name="analitics_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")})
     */
    protected $infoPages;

    /**
     * @ORM\ManyToMany(targetEntity="Vidal\DrugBundle\Entity\Company", inversedBy="analitics")
     * @ORM\JoinTable(name="analitics_companies",
     *        joinColumns={@ORM\JoinColumn(name="analitics_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="CompanyID", referencedColumnName="CompanyID")})
     */
    protected $companies;

    /**
     * @ORM\ManyToMany(targetEntity="Vidal\DrugBundle\Entity\Nozology", inversedBy="analitics")
     * @ORM\JoinTable(name="analitics_nozologies",
     *        joinColumns={@ORM\JoinColumn(name="analitics_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")})
     */
    protected $nozologies;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $dateFrom;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $dateTo;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $dateLast;

    /** @ORM\Column(type="boolean") */
    protected $process = false;

    public function __construct()
    {
        $this->molecules = new ArrayCollection();
        $this->atc = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->infoPages = new ArrayCollection();
        $this->nozologies = new ArrayCollection();
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
    public function getAtc()
    {
        return $this->atc;
    }

    /**
     * @param mixed $atc
     */
    public function setAtc($atc)
    {
        $this->atc = $atc;
    }

    /**
     * @return mixed
     */
    public function getMolecules()
    {
        return $this->molecules;
    }

    /**
     * @param mixed $molecules
     */
    public function setMolecules($molecules)
    {
        $this->molecules = $molecules;
    }

    /**
     * @return mixed
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param mixed $dateFrom
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return mixed
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param mixed $dateTo
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @return mixed
     */
    public function getDateLast()
    {
        return $this->dateLast;
    }

    /**
     * @param mixed $dateLast
     */
    public function setDateLast($dateLast)
    {
        $this->dateLast = $dateLast;
    }

    /**
     * @return ArrayCollection
     */
    public function getCompanies()
    {
        return $this->companies;
    }

    /**
     * @param ArrayCollection $companies
     */
    public function setCompanies($companies)
    {
        $this->companies = $companies;
    }

    /**
     * @return ArrayCollection
     */
    public function getInfoPages()
    {
        return $this->infoPages;
    }

    /**
     * @param ArrayCollection $infoPages
     */
    public function setInfoPages($infoPages)
    {
        $this->infoPages = $infoPages;
    }

    /**
     * @return mixed
     */
    public function getNozologies()
    {
        return $this->nozologies;
    }

    /**
     * @param mixed $nozologies
     */
    public function setNozologies($nozologies)
    {
        $this->nozologies = $nozologies;
    }

    /**
     * @return mixed
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param mixed $process
     */
    public function setProcess($process)
    {
        $this->process = $process;
    }
}