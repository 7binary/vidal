<?php

namespace Vidal\DrugBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use Vidal\DrugBundle\Entity\Tag;
use Vidal\DrugBundle\Entity\Article;
use Vidal\DrugBundle\Entity\Art;
use Vidal\DrugBundle\Entity\Publication;
use Vidal\DrugBundle\Entity\PharmArticle;

class TagTransformer implements DataTransformerInterface
{
	private $om;

	private $subject;

	public function __construct(ObjectManager $om, $subject)
	{
		$this->om      = $om;
		$this->subject = $subject;
	}

	public function transform($searchTags)
	{
		return '';
	}

	public function reverseTransform($text)
	{
		$text = trim($text);
		$text = trim($text, ';');

		if (empty($text)) {
			return null;
		}

		$tags = explode(';', $text);

		if (empty($tags)) {
			return null;
		}

		foreach ($tags as $tagText) {
			$tagText = trim($tagText);
			$tag     = $this->om->getRepository('VidalDrugBundle:Tag')->findOneByText($tagText);

			if (empty($tag)) {
				$tag = new Tag();
				$tag->setText($tagText);
				$this->om->persist($tag);
			}

			$this->subject->addTag($tag);
			$this->om->flush();
		}

		return null;
	}
}