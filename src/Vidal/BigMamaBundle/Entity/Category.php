<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="CategoryRepository")
 * @ORM\Table(name="big_mama_category")
 */
class Category extends BaseEntity
{
    /** @ORM\Column(length=500) */
    protected $title;

    /** @ORM\Column(length=500, nullable=true) */
    protected $link;

    /** @ORM\Column(length=500, nullable=true) */
    protected $linkManual;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $position;

    /** @ORM\OneToMany(targetEntity="Publication", mappedBy="category") */
    protected $publications;

    /** @ORM\OneToMany(targetEntity="Video", mappedBy="category") */
    protected $videos;

    /** @ORM\OneToMany(targetEntity="Video", mappedBy="category") */
    protected $audios;

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->created = $now;
        $this->updated = $now;

        $this->publications = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->audios = new ArrayCollection();
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
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
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

    /**
     * @return mixed
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * @param mixed $videos
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;
    }

    /**
     * @return mixed
     */
    public function getAudios()
    {
        return $this->audios;
    }

    /**
     * @param mixed $audios
     */
    public function setAudios($audios)
    {
        $this->audios = $audios;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getLinkManual()
    {
        return $this->linkManual;
    }

    /**
     * @param mixed $linkManual
     */
    public function setLinkManual($linkManual)
    {
        $this->linkManual = $linkManual;
    }
}