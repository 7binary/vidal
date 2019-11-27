<?php

namespace Vidal\MainBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Vidal\MainBundle\Entity\City;

class CityToStringTransformer implements DataTransformerInterface
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

	/**
	 * Transforms an object to a string (id).
	 *
	 * @param  City|null $city
	 * @return string
	 */
	public function transform($city)
	{
		if (null === $city) {
			return '';
		}

		$title = $city->getTitle();

		if ($region = $city->getRegion()) {
			$title .= ', ' . $region->getTitle();
		}
		if ($country = $city->getCountry()) {
			$title .= ', ' . $country->getTitle();
		}

		return $title;
	}

	/**
	 * Transforms a string (id) to an object (city).
	 */
	public function reverseTransform($string)
	{
		if (empty($string)) {
			return null;
		}

		$titles  = explode(',', $string);
		$city    = trim($titles[0]);
		$region  = null;
		$country = null;

		if (isset($titles[2])) {
			$region  = trim($titles[1]);
			$country = trim($titles[2]);
		}

		$builder = $this->om->createQueryBuilder();

		$builder
			->select('city')
			->from('VidalMainBundle:City', 'city')
			->leftJoin('VidalMainBundle:Country', 'country', 'WITH', 'country = city.country')
			->where('city.title = :city')
			->orderBy('country.id', 'ASC')
			->setParameter('city', $city)
			->setMaxResults(1);

		if ($country) {
			$builder
				->leftJoin('city.country', 'c')
				->leftJoin('city.region', 'r')
				->andWhere('c.title LIKE :country')
				->andWhere('r.title LIKE :region')
				->setParameter('country', $country)
				->setParameter('region', $region);
		}

		$city = $builder->getQuery()->getOneOrNullResult();

		if (empty($city)) {
			throw new TransformationFailedException(sprintf('Город "%s" не найден!', $string));
		}

		return $city;
	}
}