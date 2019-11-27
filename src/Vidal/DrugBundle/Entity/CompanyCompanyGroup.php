<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity @ORM\Table(name="company_companygroup") */
class CompanyCompanyGroup
{
	/** @ORM\Column(type="integer") @ORM\Id */
	protected $CompanyID;

	/** @ORM\Column(type="integer") @ORM\Id */
	protected $CompanyGroupID;

	/**
	 * @param mixed $CompanyGroupID
	 */
	public function setCompanyGroupID($CompanyGroupID)
	{
		$this->CompanyGroupID = $CompanyGroupID;
	}

	/**
	 * @return mixed
	 */
	public function getCompanyGroupID()
	{
		return $this->CompanyGroupID;
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
}