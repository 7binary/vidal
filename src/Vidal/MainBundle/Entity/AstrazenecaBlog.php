<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="AstrazenecaBlogRepository") @ORM\Table() @Filestore\Uploadable */
class AstrazenecaBlog extends BaseEntity
{
	/** @ORM\Column(type="text") */
	protected $text;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @Filestore\UploadableField(mapping="blog")
	 */
	protected $photo;

	public function __toString()
	{
		return $this->getId() . '';
	}

	/**
	 * @param mixed $photo
	 */
	public function setPhoto($photo)
	{
		$this->photo = $photo;
	}

	/**
	 * @return mixed
	 */
	public function getPhoto()
	{
		return $this->photo;
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
}