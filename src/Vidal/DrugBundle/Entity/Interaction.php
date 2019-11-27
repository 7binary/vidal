<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity(repositoryClass="InteractionRepository") @ORM\Table(name="interaction") */
class Interaction
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/** @ORM\Column(length=255, nullable=true) */
	protected $RusName;

	/** @ORM\Column(length=255, nullable=true) */
	protected $EngName;

	/** @ORM\Column(type="text", nullable=true) */
	protected $text;

    /** @ORM\Column(type="text", nullable=true) */
    protected $textLinked;

	public function __toString()
	{
		return $this->RusName;
	}

	/**
	 * @param mixed $EngName
	 */
	public function setEngName($EngName)
	{
		$this->EngName = $EngName;
	}

	/**
	 * @return mixed
	 */
	public function getEngName()
	{
		return $this->EngName;
	}

	/**
	 * @param mixed $RusName
	 */
	public function setRusName($RusName)
	{
		$this->RusName = $RusName;
	}

	/**
	 * @return mixed
	 */
	public function getRusName()
	{
		return $this->RusName;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}

	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->text;
	}

    /**
     * @return mixed
     */
    public function getTextLinked()
    {
        return $this->textLinked;
    }

    /**
     * @param mixed $textLinked
     */
    public function setTextLinked($textLinked)
    {
        $this->textLinked = $textLinked;
    }
}