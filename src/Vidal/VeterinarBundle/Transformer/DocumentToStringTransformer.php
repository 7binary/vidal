<?php

namespace Vidal\VeterinarBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Vidal\DrugBundle\Entity\Document;
use Vidal\DrugBundle\Entity\Publication;
use Vidal\DrugBundle\Entity\Art;
use Vidal\DrugBundle\Entity\Article;

class DocumentToStringTransformer implements DataTransformerInterface
{
	private $em;
	private $object;

	public function __construct(ObjectManager $em, $object)
	{
		$this->em     = $em;
		$this->object = $object;
	}

	public function transform($document)
	{
		return $document ? $document->getDocumentID() . '' : '';
	}

	public function reverseTransform($text)
	{
		$text = trim($text);

		if (empty($text)) {
			return null;
		}

		$id = intval($text);

		$document = $this->em->createQuery('
			SELECT d
			FROM VidalVeterinarBundle:Document d
			WHERE d.DocumentID = :id
		')->setParameter('id', $id)
			->getOneOrNullResult();

		return $document;
	}
}