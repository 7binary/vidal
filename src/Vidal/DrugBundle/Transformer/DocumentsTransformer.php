<?php

namespace Vidal\DrugBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Vidal\DrugBundle\Entity\Document;

class DocumentsTransformer implements DataTransformerInterface
{
	private $em;

	public function __construct(ObjectManager $em)
	{
		$this->em = $em;
	}

	public function transform($documents)
	{
		if (count($documents) < 1) {
			return '';
		}

		$documentIds = array();

		foreach ($documents as $document) {
			$documentIds[] = $document->getDocumentID();
		}

		return implode(';', $documentIds);
	}

	public function reverseTransform($text)
	{
		$documentIds = explode(';', trim($text));

		$documents = $this->em->createQuery('
			SELECT d
			FROM VidalDrugBundle:Document d
			WHERE d.DocumentID IN (:ids)
		')->setParameter('ids', $documentIds)
			->getResult();

		return new ArrayCollection($documents);
	}
}