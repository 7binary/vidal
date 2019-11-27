<?php

namespace Vidal\MainBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class YearToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($year)
    {
        if (null === $year) {
            return 0;
        }

        return (int)$year->format('Y');
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $year = new \DateTime($id . '-01-01');

        return $year;
    }
}