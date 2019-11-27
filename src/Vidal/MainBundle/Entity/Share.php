<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity(repositoryClass="ShareRepository") @ORM\Table(name="share") */
class Share extends BaseEntity
{
	/** @ORM\Column(type="integer") */
	protected $target;

	/** @ORM\Column(type="string", length=255) */
	protected $class;

	/**
	 * @return mixed
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * @param mixed $target
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}

	/**
	 * @return mixed
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @param mixed $class
	 */
	public function setClass($class)
	{
		$this->class = $class;
	}
}