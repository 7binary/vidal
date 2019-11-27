<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="SearchRepository") @ORM\Table(name="search") */
class Search
{
	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/**
	 * @ORM\Column(length=100)
	 * @Assert\NotBlank(message="Укажите поисковый запрос")
	 */
	protected $query;

	/**
	 * @ORM\Column(length=500, nullable=true)
	 */
	protected $referer = null;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $withoutResults = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $tooShort = false;

	public function __construct()
	{
		$this->created = new \DateTime();
	}

	public function __toString()
	{
		return $this->query;
	}

	/**
	 * @return mixed
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * @param mixed $query
	 */
	public function setQuery($query)
	{
		$this->query = $query;
	}

	/**
	 * @return mixed
	 */
	public function getReferer()
	{
		return $this->referer;
	}

	/**
	 * @param mixed $referer
	 */
	public function setReferer($referer)
	{
		$this->referer = $referer;
	}

	/**
	 * @return mixed
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param mixed $created
	 */
	public function setCreated($created)
	{
		$this->created = $created;
	}

	/**
	 * @return mixed
	 */
	public function getWithoutResults()
	{
		return $this->withoutResults;
	}

	/**
	 * @param mixed $withoutResults
	 */
	public function setWithoutResults($withoutResults)
	{
		$this->withoutResults = $withoutResults;
	}

	/**
	 * @return mixed
	 */
	public function getTooShort()
	{
		return $this->tooShort;
	}

	/**
	 * @param mixed $tooShort
	 */
	public function setTooShort($tooShort)
	{
		$this->tooShort = $tooShort;
	}
}