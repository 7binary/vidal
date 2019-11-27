<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="module") */
class Module
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(type="text", nullable=true) */
	protected $body;

	/** @ORM\Column(type="boolean") */
	protected $enabled = true;

	/** @ORM\Column(length=255, nullable=true) */
	protected $help;

	public function __toString()
	{
		return '';
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
	 * @param mixed $help
	 */
	public function setHelp($help)
	{
		$this->help = $help;
	}

	/**
	 * @return mixed
	 */
	public function getHelp()
	{
		return $this->help;
	}
}