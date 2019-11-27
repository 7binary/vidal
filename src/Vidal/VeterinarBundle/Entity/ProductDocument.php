<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity @ORM\Table(name="product_document") */
class ProductDocument
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\ManyToOne(targetEntity="Document", inversedBy="productDocument") */
	protected $DocumentID;

	/** @ORM\Id @ORM\Column(type="integer") @ORM\ManyToOne(targetEntity="Product", inversedBy="productDocument") */
	protected $ProductID;

	/** @ORM\Column(type="smallint", nullable=true) */
	protected $Ranking;
}