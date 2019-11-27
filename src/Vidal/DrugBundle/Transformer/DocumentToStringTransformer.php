<?php

namespace Vidal\DrugBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;

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
	    try {
            return $document ? $document->getDocumentID() . '' : '';
        }
        catch (\Exception $e) {
	        return '';
        }
	}

	public function reverseTransform($text)
	{
		$text = trim($text);

		if (empty($text)) {
			return null;
		}

		$id = intval($text);

		return $this->em->createQuery('
			SELECT d
			FROM VidalDrugBundle:Document d
			WHERE d.DocumentID = :id
		')->setParameter('id', $id)
			->getOneOrNullResult();
	}
}