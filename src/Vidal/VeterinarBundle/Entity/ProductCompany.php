<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="product_company") */
class ProductCompany
{
	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Product", inversedBy="productCompany")
	 * @ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")
	 */
	protected $ProductID;

	/**
	 * @ORM\Id
	 * @ORM\ManyToOne(targetEntity="Company", inversedBy="productCompany")
	 * @ORM\JoinColumn(name="CompanyID", referencedColumnName="CompanyID")
	 */
	protected $CompanyID;

	/** @ORM\Column(length=255, nullable=true) */
	protected $CompanyRusNote;

	/** @ORM\Column(length=255, nullable=true) */
	protected $CompanyEngNote;

	/** @ORM\Column(type="boolean") */
	protected $ItsMainCompany = false;

	/** @ORM\Column(type="boolean") */
	protected $ShowInList = false;

	/** @ORM\Column(type="smallint", nullable=true) */
	protected $Ranking;

	public function __toString()
	{
		return $this->CompanyRusNote ? $this->CompanyRusNote : $this->CompanyEngNote;
	}

	/**
	 * @param mixed $CompanyEngNote
	 */
	public function setCompanyEngNote($CompanyEngNote)
	{
		$this->CompanyEngNote = $CompanyEngNote;
	}

	/**
	 * @return mixed
	 */
	public function getCompanyEngNote()
	{
		return $this->CompanyEngNote;
	}

	/**
	 * @param mixed $CompanyID
	 */
	public function setCompanyID($CompanyID)
	{
		$this->CompanyID = $CompanyID;
	}

	/**
	 * @return mixed
	 */
	public function getCompanyID()
	{
		return $this->CompanyID;
	}

	/**
	 * @param mixed $CompanyRusNote
	 */
	public function setCompanyRusNote($CompanyRusNote)
	{
		$this->CompanyRusNote = $CompanyRusNote;
	}

	/**
	 * @return mixed
	 */
	public function getCompanyRusNote()
	{
		return $this->CompanyRusNote;
	}

	/**
	 * @param mixed $ItsMainCompany
	 */
	public function setItsMainCompany($ItsMainCompany)
	{
		$this->ItsMainCompany = $ItsMainCompany;
	}

	/**
	 * @return mixed
	 */
	public function getItsMainCompany()
	{
		return $this->ItsMainCompany;
	}

	/**
	 * @param mixed $ProductID
	 */
	public function setProductID($ProductID)
	{
		$this->ProductID = $ProductID;
	}

	/**
	 * @return mixed
	 */
	public function getProductID()
	{
		return $this->ProductID;
	}

	/**
	 * @param mixed $Ranking
	 */
	public function setRanking($Ranking)
	{
		$this->Ranking = $Ranking;
	}

	/**
	 * @return mixed
	 */
	public function getRanking()
	{
		return $this->Ranking;
	}

	/**
	 * @param mixed $ShowInList
	 */
	public function setShowInList($ShowInList)
	{
		$this->ShowInList = $ShowInList;
	}

	/**
	 * @return mixed
	 */
	public function getShowInList()
	{
		return $this->ShowInList;
	}
}