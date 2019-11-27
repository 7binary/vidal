<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="AstrazenecaNewRepository") @ORM\Table() @FileStore\Uploadable */
class AstrazenecaNew extends BaseEntity
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $anons;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $body;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @FileStore\UploadableField(mapping="photo")
	 * @Assert\Image(
	 *      maxSize="4M",
	 *    	maxSizeMessage="Принимаются фотографии размером до 4 Мб"
	 * )
	 */
	protected $photo;

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
     * @param mixed $anons
     */
    public function setAnons($anons)
    {
        $this->anons = $anons;
    }

    /**
     * @return mixed
     */
    public function getAnons()
    {
        return $this->anons;
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
}