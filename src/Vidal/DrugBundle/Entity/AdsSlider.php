<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity() @ORM\Table(name="ads_slider") @FileStore\Uploadable */
class AdsSlider extends BaseEntity
{
    /** @ORM\ManyToOne(targetEntity="Ads", inversedBy="sliders") */
    protected $ads;

    /** @ORM\ManyToOne(targetEntity="Art", inversedBy="sliders") */
    protected $art;

    /** @ORM\ManyToOne(targetEntity="Article", inversedBy="sliders") */
    protected $article;

    /** @ORM\Column(type="integer") */
    protected $priority = 1;

    /** @ORM\Column(type="integer") */
    protected $slideNumber = 1;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="video")
     * @Assert\File(
     *        maxSize="400M",
     *        maxSizeMessage="Видео не может быть больше 512Мб",
     *        mimeTypes={"video/mp4"},
     *        mimeTypesMessage="Загружаемое видео может иметь лишь mp4 формат"
     * )
     */
    protected $video;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $videoWidth;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $videoHeight;

    /** @ORM\Column(type="boolean") */
    protected $videoForUsersOnly = false;

    /** @ORM\Column(type="text", nullable=true) */
    protected $raw;

    /**
     * @return mixed
     */
    public function getAds()
    {
        return $this->ads;
    }

    /**
     * @param mixed $ads
     */
    public function setAds($ads)
    {
        $this->ads = $ads;
    }

    /**
     * @return mixed
     */
    public function getArt()
    {
        return $this->art;
    }

    /**
     * @param mixed $art
     */
    public function setArt($art)
    {
        $this->art = $art;
    }

    /**
     * @return mixed
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param mixed $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getSlideNumber()
    {
        return $this->slideNumber;
    }

    /**
     * @param mixed $slideNumber
     */
    public function setSlideNumber($slideNumber)
    {
        $this->slideNumber = $slideNumber;
    }

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
    public function getVideoForUsersOnly()
    {
        return $this->videoForUsersOnly;
    }

    /**
     * @param mixed $videoForUsersOnly
     */
    public function setVideoForUsersOnly($videoForUsersOnly)
    {
        $this->videoForUsersOnly = $videoForUsersOnly;
    }

    /**
     * @return mixed
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @param mixed $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }
}