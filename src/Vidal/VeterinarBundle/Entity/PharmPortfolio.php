<?php

namespace Vidal\VeterinarBundle\Entity;

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
	/** @ORM\Column(length=255, unique=true) */
	protected $url;

	/** @ORM\Column(length=255) */
	protected $title;

	/** @ORM\Column(type="text") */
	protected $body;

	/**
	 * @ORM\ManyToOne(targetEntity="Document", inversedBy="portfolios")
	 * @ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")
	 */
	protected $DocumentID;

	public function __toString()
	{
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->url;
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
	public function getBody()
	{
		return $this->body;
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
	public function getDocumentID()
	{
		return $this->DocumentID;
	}

	/**
	 * @param mixed $DocumentID
	 */
	public function setDocumentID($DocumentID)
	{
		$this->DocumentID = $DocumentID;
	}
}