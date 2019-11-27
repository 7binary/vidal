<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="ClinicoPhPointersRepository") @ORM\Table(name="clinicophpointers") */
class ClinicoPhPointers
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    protected $ClPhPointerID;

    /** @ORM\Column(length=50) */
    protected $Code;

    /** @ORM\Column(length=255) */
    protected $Name;

    /** @ORM\Column(type="smallint") */
    protected $Level;

    /** @ORM\Column(type="boolean") */
    protected $ShowInExport = false;

    /** @ORM\Column(type="boolean") */
    protected $UsedInSp = false;

    /**
     * @ORM\ManyToMany(targetEntity="Document", mappedBy="clphPointers")
     * @ORM\JoinTable(name="document_clphpointers",
     *        joinColumns={@ORM\JoinColumn(name="ClPhPointerID", referencedColumnName="ClPhPointerID")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")})
     */
    protected $documents;

    /** @ORM\Column(length=255) */
    protected $url;

    /** @ORM\Column(type="integer") */
    protected $countProducts = 0;

    /**
     * @ORM\ManyToOne(targetEntity="ClinicoPhPointers", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="ClPhPointerID")
     */
    protected $parent;

    /** @ORM\OneToMany(targetEntity="ClinicoPhPointers", mappedBy="parent") */
    protected $children;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->Code . ' - ' . $this->Name;
    }

    /**
     * @param mixed $ClPhPointerID
     */
    public function setClPhPointerID($ClPhPointerID)
    {
        $this->ClPhPointerID = $ClPhPointerID;
    }

    /**
     * @return mixed
     */
    public function getClPhPointerID()
    {
        return $this->ClPhPointerID;
    }

    /**
     * @param mixed $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param mixed $Level
     */
    public function setLevel($Level)
    {
        $this->Level = $Level;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->Level;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $ShowInExport
     */
    public function setShowInExport($ShowInExport)
    {
        $this->ShowInExport = $ShowInExport;
    }

    /**
     * @return mixed
     */
    public function getShowInExport()
    {
        return $this->ShowInExport;
    }

    /**
     * @param mixed $documents
     */
    public function setDocuments(ArrayCollection $documents)
    {
        $this->documents = $documents;
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param mixed $UsedInSp
     */
    public function setUsedInSp($UsedInSp)
    {
        $this->UsedInSp = $UsedInSp;
    }

    /**
     * @return mixed
     */
    public function getUsedInSp()
    {
        return $this->UsedInSp;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $countProducts
     */
    public function setCountProducts($countProducts)
    {
        $this->countProducts = $countProducts;
    }

    /**
     * @return mixed
     */
    public function getCountProducts()
    {
        return $this->countProducts;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getId()
    {
        return $this->ClPhPointerID;
    }
}