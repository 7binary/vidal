<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @inheritdoc
 * @ORM\Entity(repositoryClass="VideoRepository")
 * @ORM\Table(name="big_mama_video")
 */
class Video extends BaseMediaEntity
{
    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="big_mama")
     * @Assert\File(
     *        maxSize="100M",
     *        maxSizeMessage="Видео не может быть больше 100Мб",
     *        mimeTypesMessage="Видео должно быть в формате .mp4"
     * )
     */
    protected $video;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $videoWidth;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $videoHeight;

    /** @ORM\ManyToOne(targetEntity="Category", inversedBy="videos") */
    protected $category;

    /**
     * @return mixed
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param mixed $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

    /**
     * @return mixed
     */
    public function getVideoWidth()
    {
        return $this->videoWidth;
    }

    /**
     * @param mixed $videoWidth
     */
    public function setVideoWidth($videoWidth)
    {
        $this->videoWidth = $videoWidth;
    }

    /**
     * @return mixed
     */
    public function getVideoHeight()
    {
        return $this->videoHeight;
    }

    /**
     * @param mixed $videoHeight
     */
    public function setVideoHeight($videoHeight)
    {
        $this->videoHeight = $videoHeight;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}