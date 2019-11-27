<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="specialization") */
class Specialization 
{
	/** @ORM\Id @ORM\Column(type = "integer") @ORM\GeneratedValue */
	protected $id;

	/** @ORM\OneToMany(targetEntity="User", mappedBy="specialization") */
	protected $users;
	
	/**
	 * @ORM\Column(type="string")
	 * @Assert\NotBlank(message = "Укажите название специализации.")
	 * @Assert\Length(max = 127, maxMessage = "Название специализации не может быть длиннее {{limit}} знаков.")
	 */
	protected $title;
	
	public function __construct()
	{
		$this->users = new ArrayCollection();
	}
	
	public function __toString()
	{
		return $this->title;
	}
	
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getUsers()
	{
		return $this->users;
	}

	/**
	 * @param mixed $users
	 */
	public function setUsers($users)
	{
		$this->users = $users;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title)
	{
		$this->title = $title;
		
		return $this;
	}
}