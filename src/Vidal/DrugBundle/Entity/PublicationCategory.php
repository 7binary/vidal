<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="PublicationCategoryRepository") @ORM\Table(name="publication_category") */
class PublicationCategory extends BaseEntity
{
    /** @ORM\Column(length=500) */
    protected $title;

    /** @ORM\Column(length=500, nullable=true) */
    protected $project;

    /** @ORM\ManyToMany(targetEntity="Publication", inversedBy="categories", cascade={"persist"}) */
    protected $publications;

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->created = $now;
        $this->updated = $now;

        $this->publications = new ArrayCollection();
    }

    public function __toString()
    {
        return empty($this->title) ? '' : $this->title;
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
    public function getPublications()
    {
        return $this->publications;
    }

    /**
     * @param mixed $publications
     */
    public function setPublications($publications)
    {
        $this->publications = $publications;
    }

    public function addPublication(Publication $p)
    {
        $this->publications[] = $p;

        return $this;
    }

    public function removePublication(Publication $p)
    {
        $this->publications->removeElement($p);
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }
}