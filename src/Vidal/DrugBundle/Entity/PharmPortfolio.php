<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="PharmPortfolioRepository")
 * @ORM\Table(name="pharm_portfolio")
 * @UniqueEntity("url")
 * @FileStore\Uploadable
 */
class PharmPortfolio extends BaseEntity
{
	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/** @ORM\Column(length=255, unique=true) */
	protected $url;

	/** @ORM\Column(length=255) */
	protected $title;

	/** @ORM\Column(type="text") */
	protected $body;

	/** @ORM\ManyToOne(targetEntity="PharmCompany", inversedBy="portfolios") */
	protected $company;

	/**
	 * @ORM\ManyToOne(targetEntity="Document", inversedBy="portfolios")
	 * @ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")
	 */
	protected $DocumentID;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @FileStore\UploadableField(mapping="video")
	 * @Assert\File(
	 *        maxSize="100M",
	 *        maxSizeMessage="Видео не может быть больше 100Мб",
	 *        mimeTypesMessage="Видео должно быть в формате .flv"
	 * )
	 */
	protected $video;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $videoWidth;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $videoHeight;

	/** @ORM\ManyToMany(targetEntity="Video", inversedBy="portfolios", cascade={"persist"}) */
	protected $videos;

	public function __toString()
	{
		return $this->title;
	}

	public function __construct()
	{
		$this->videos = new ArrayCollection();
	}

	/**
	 * @param mixed $DocumentID
	 */
	public function setDocumentID($DocumentID)
	{
		$this->DocumentID = $DocumentID;
	}

	/**
	 * @return mixed
	 */
	public function getDocumentID()
	{
		return $this->DocumentID;
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
	 * @param mixed $company
	 */
	public function setCompany($company)
	{
		$this->company = $company;
	}

	/**
	 * @return mixed
	 */
	public function getCompany()
	{
		return $this->company;
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
	public function getPriority()
	{
		return $this->priority;
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
	 * @param mixed $video
	 */
	public function setVideo($video)
	{
		$this->video = $video;
	}

	/**
	 * @return mixed
	 */
	public function getVideo()
	{
		return $this->video;
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
	public function getVideoHeight()
	{
		return $this->videoHeight;
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
	public function getVideoWidth()
	{
		return $this->videoWidth;
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
	public function getVideos()
	{
		return $this->videos;
	}

	public function addVideo(Video $video)
	{
		if (!$this->videos->contains($video)) {
			$this->videos[] = $video;
		}

		return $this;
	}

	public function removeVideo(Video $video)
	{
		$this->videos->removeElement($video);
	}
}