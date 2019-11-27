<?php
namespace Vidal\VeterinarBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PictureRepository extends EntityRepository
{
	public function findByDocumentID($DocumentID)
	{
		return $this->_em->createQuery('
			SELECT p.PathForElectronicEdition path
			FROM VidalVeterinarBundle:Picture p
			JOIN p.infoPages ip
			JOIN VidalVeterinarBundle:DocumentInfoPage dip WITH dip.InfoPageID = ip
			WHERE dip.DocumentID = :DocumentID AND
				ip.CountryCode = \'RUS\'
			ORDER BY dip.Ranking DESC
		')->setParameter('DocumentID', $DocumentID)
			->getResult();
	}

	public function findByProductIds($productIds)
	{
		$picturesRaw = $this->_em->createQuery('
			SELECT pict.PathForElectronicEdition path, prod.ProductID
			FROM VidalVeterinarBundle:Picture pict
			JOIN VidalVeterinarBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
			JOIN VidalVeterinarBundle:Product prod WITH pp.ProductID = prod.ProductID
			WHERE prod.ProductID IN (:productIds)
				AND pp.CountryEditionCode = \'RUS\'
				AND pp.YearEdition >= 2017
			ORDER BY prod.ProductID DESC, pp.YearEdition DESC
		')->setParameter('productIds', $productIds)
			->getResult();

		$pictures = array();

		for ($i=0; $i<count($picturesRaw); $i++) {
			$key = $picturesRaw[$i]['ProductID'];
			if (!isset($pictures[$key])) {
                $parts = explode("\\", $picturesRaw[$i]['path']);
                $last = count($parts) - 1;
                $pictures[$key] = strtolower($parts[$last]);
			}
		}

		return $pictures;
	}

	public function findAllByProductIds($productIds)
	{
		$picturesRaw = $this->_em->createQuery('
			SELECT pict.PathForElectronicEdition path, prod.ProductID
			FROM VidalVeterinarBundle:Picture pict
			JOIN VidalVeterinarBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
			JOIN VidalVeterinarBundle:Product prod WITH pp.ProductID = prod.ProductID
			WHERE prod.ProductID IN (:productIds)
				AND pp.CountryEditionCode = \'RUS\'
			ORDER BY prod.ProductID DESC, pp.YearEdition DESC
		')->setParameter('productIds', $productIds)
			->getResult();

		$pictures = array();

		for ($i=0; $i<count($picturesRaw); $i++) {
		    $parts = explode("\\", $picturesRaw[$i]['path']);
		    $last = count($parts) - 1;
			$pictures[] = strtolower($parts[$last]);
		}

		return array_unique($pictures);
	}

	public function findByInfoPageID($InfoPageID)
	{
		$picture = $this->_em->createQuery('
			SELECT p.PathForElectronicEdition path
			FROM VidalVeterinarBundle:Picture p
			JOIN p.infoPages i WITH i = :InfoPageID
		')->setParameter('InfoPageID', $InfoPageID)
			->setMaxResults(1)
			->getOneOrNullResult();

		if (!empty($picture)) {
			$picture['path'] = $path = preg_replace('/.+\\\\JPG\\\\/', '', $picture['path']);
		}

		return $picture;
	}
}