<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/** @ORM\Entity(repositoryClass="TagHistoryRepository") @ORM\Table(name="tag_history") */
class TagHistory extends BaseEntity
{
	/** @ORM\Column(length=255) */
	protected $text;

	/** @ORM\ManyToOne(targetEntity="Tag", inversedBy="history") */
	protected $tag;

	/** @ORM\Column(type="array") */
	protected $articleIds = array();

	/** @ORM\Column(type="array") */
	protected $artIds = array();

	/** @ORM\Column(type="array") */
	protected $publicationIds = array();

	/** @ORM\Column(type="array") */
	protected $pharmIds = array();

	public function __toString()
	{
		return $this->text;
	}

	/**
	 * @param mixed $tag
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;
	}

	/**
	 * @return mixed
	 */
	public function getTag()
	{
		return $this->tag;
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

	public function addArticleId($id)
	{
		$this->articleIds[] = $id;
	}

	public function addArtId($id)
	{
		$this->artIds[] = $id;
	}

	public function addPublicationId($id)
	{
		$this->publicationIds[] = $id;
	}

	public function addPharmId($id)
	{
		$this->pharmIds[] = $id;
	}

	/**
	 * @param mixed $artIds
	 */
	public function setArtIds($artIds)
	{
		$this->artIds = $artIds;
	}

	/**
	 * @return mixed
	 */
	public function getArtIds()
	{
		return $this->artIds;
	}

	/**
	 * @param mixed $articleIds
	 */
	public function setArticleIds($articleIds)
	{
		$this->articleIds = $articleIds;
	}

	/**
	 * @return mixed
	 */
	public function getArticleIds()
	{
		return $this->articleIds;
	}

	/**
	 * @param mixed $publicationIds
	 */
	public function setPublicationIds($publicationIds)
	{
		$this->publicationIds = $publicationIds;
	}

	/**
	 * @return mixed
	 */
	public function getPublicationIds()
	{
		return $this->publicationIds;
	}

	/**
	 * @param mixed $pharmIds
	 */
	public function setPharmIds($pharmIds)
	{
		$this->pharmIds = $pharmIds;
	}

	/**
	 * @return mixed
	 */
	public function getPharmIds()
	{
		return $this->pharmIds;
	}

	public function preview()
	{
		$articleIds     = count($this->articleIds);
		$artIds         = count($this->artIds);
		$publicationIds = count($this->publicationIds);

		return "(новостей: $publicationIds | статей энциклопедии: $articleIds | статей специалистам: $artIds )";
	}
}