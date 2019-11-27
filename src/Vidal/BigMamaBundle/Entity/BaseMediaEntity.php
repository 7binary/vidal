<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/** @FileStore\Uploadable */
class BaseMediaEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="big_mama")
     * @Assert\Image(
     *    maxSize="8M",
     *    maxSizeMessage="Принимаются фотографии размером до 8 Мб"
     * )
     */
    protected $photo;

    /** @ORM\Column(length=500) */
    protected $title;

    /** @ORM\Column(type="text", nullable=true) */
    protected $announce;

    /** @ORM\Column(type="text") */
    protected $body;

    /** @ORM\Column(type="text", nullable=true) */
    protected $bodyLinked;

    /** @ORM\Column(length=500, nullable=true) */
    protected $keyword;

    /** @ORM\Column(length=500, nullable=true) */
    protected $link;

    /** @ORM\Column(length=500, nullable=true) */
    protected $linkManual;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $date;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $position;

    public function __construct()
    {
        $now = new \DateTime('now');
        $this->created = $now;
        $this->updated = $now;
        $this->date = $now;
    }

    public function __toString()
    {
        return empty($this->title) ? '' : $this->title;
    }

    /**
     * @param mixed $announce
     */
    public function setAnnounce($announce)
    {
        $this->announce = $announce;
    }

    /**
     * @return mixed
     */
    public function getAnnounce()
    {
        return $this->announce;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $title = str_replace('<p>', '', $title);
        $title = str_replace('</p>', '', $title);
        $title = str_replace('<div>', '', $title);
        $title = str_replace('</div>', '', $title);

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
     * @param mixed $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
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

    /**
     * @return mixed
     */
    public function getBodyLinked()
    {
        return $this->bodyLinked;
    }

    /**
     * @param mixed $bodyLinked
     */
    public function setBodyLinked($bodyLinked)
    {
        $this->bodyLinked = $bodyLinked;
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
}