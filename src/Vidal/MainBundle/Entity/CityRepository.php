<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CityRepository extends EntityRepository
{
	const DISPLAYED_CITIES_AJAX = 10;

	public function getChoices()
	{
		$raw = $this->_em->createQuery('
			SELECT c.id, c.title, SIZE(c.doctors) as total, r.title as region, co.title as country
			FROM VidalMainBundle:City c
			JOIN c.region r
			JOIN c.country co
			WHERE c.doctors IS NOT EMPTY
			ORDER BY c.title ASC
		')->getResult();

		$cities = array();

		foreach ($raw as $r) {
			$key          = $r['id'];
			$cities[$key] = '[' . $r['total'] . '] ' . $r['title'] . ' -> ' . $r['region'] . ' -> ' . $r['country'];
		}

		return $cities;
	}

	public function getNames()
	{
		$raw = $this->_em->createQuery("
		 	SELECT c.title as city, r.title as region, co.title as country
		 	FROM VidalMainBundle:City c
		 	JOIN c.region r
		 	JOIN c.country co
		 	WHERE c.title != ''
		 	ORDER BY c.title ASC
		")
			->getResult();

		$names = array();
		foreach ($raw as $r) {
			$name  = trim($r['city']);
			$title = $name;

			if (!empty($r['region'])) {
				$title .= ', ' . trim($r['region']);
			}

			if (!empty($r['country'])) {
				$title .= ', ' . trim($r['country']);
			}

			$names[$title] = array('name' => $name, 'title' => $title);
		}

		return array_values($names);
	}

	public function findByName($name)
	{
		return $this->_em->createQuery('
			SELECT c
			FROM VidalMainBundle:City c
			WHERE c.title LIKE :name
			ORDER BY c.title ASC
		')->setParameter('name', $name)
			->setMaxResults(1)
			->getOneOrNullResult();
	}

    public function getBlankCity()
    {
        return $this->_em->createQuery('
			SELECT c
			FROM VidalMainBundle:City c
			WHERE c.id = :id
		')->setParameter('id', 5568853)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

	public function findAnyByName($name)
	{
		return $this->_em->createQuery('
			SELECT c
			FROM VidalMainBundle:City c
			WHERE c.title LIKE :name
			ORDER BY c.title ASC
		')->setParameter('name', '%' . $name . '%')
			->setMaxResults(1)
			->getOneOrNullResult();
	}


	public function findAutocomplete($term)
	{
		$term = $term . '%';

		$raw = $this->_em->createQuery('
			SELECT c.title city, r.title region, co.title country
			FROM VidalMainBundle:City c
			LEFT JOIN c.region r
			LEFT JOIN c.country co
			WHERE c.title LIKE :term
			ORDER BY c.title ASC
		')
			->setParameter('term', $term)
			->setMaxResults(self::DISPLAYED_CITIES_AJAX)
			->getResult();

		$titles = array();

		foreach ($raw as $r) {
			$title = $r['city'];

			if (!empty($r['region'])) {
				$title .= ', ' . $r['region'];
			}

			if (!empty($r['country'])) {
				$title .= ', ' . $r['country'];
			}

			$titles[] = $title;
		}

		return $titles;
	}
}