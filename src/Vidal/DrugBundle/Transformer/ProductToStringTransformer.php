<?php

namespace Vidal\DrugBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProductToStringTransformer implements DataTransformerInterface
{
	private $em;
	private $object;

	public function __construct(ObjectManager $em, $object)
	{
		$this->em     = $em;
		$this->object = $object;
	}

	public function transform($product)
	{
		return $product ? $product->getProductID() . '' : '';
	}

	public function reverseTransform($text)
	{
		$text = trim($text);

		if (empty($text)) {
			return null;
		}

		$id = intval($text);

		return $this->em->createQuery('
			SELECT p
			FROM VidalDrugBundle:Product p
			WHERE p.ProductID = :id
		')->setParameter('id', $id)
			->getOneOrNullResult();
	}
}