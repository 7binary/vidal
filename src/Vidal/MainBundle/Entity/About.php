<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="AboutRepository") @ORM\Table(name="about") */
class About
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/**
	 * @ORM\Column(length=500)
	 * @Assert\NotBlank(message="Укажите название раздела компании")
	 */
	protected $title;

	/**
	 * @ORM\Column(length=50)
	 * @Assert\NotBlank(message="Укажите название страницы")
	 */
	protected $url;

	/**
	 * @ORM\Column(type="text")
	 * @Assert\NotBlank(message="Укажите содержимое раздела компании")
	 */
	protected $body;

	/** @ORM\Column(type="boolean") */
	protected $enabled;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	public function __construct()
	{
		$this->enabled = true;
	}

	public function __toString()
	{
		return $this->title;
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
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
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
	 * @param mixed $enabled
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @return mixed
	 */
	public function getEnabled()
	{
		return $this->enabled;
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
}