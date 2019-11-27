<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PictureRepository extends EntityRepository
{
    public function findByDocumentID($DocumentID)
    {
        return $this->_em->createQuery('
			SELECT p.PathForElectronicEdition path
			FROM VidalDrugBundle:Picture p
			JOIN p.infoPages i
			JOIN i.documents d
			WHERE dip.DocumentID = :DocumentID AND
				ip.CountryCode = \'RUS\'
			ORDER BY dip.Ranking DESC
		')->setParameter('DocumentID', $DocumentID)
            ->getResult();
    }

    public function findAllByProductIds($productIds, $year = null)
    {
        # эти вне правила - условие от Максимилиана
        $extraRaw = $this->_em->createQuery("
            SELECT pp.productpicture
            FROM VidalDrugBundle:Picture pict
            JOIN VidalDrugBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
            JOIN VidalDrugBundle:Product prod WITH pp.ProductID = prod.ProductID
            JOIN prod.document d
            JOIN VidalDrugBundle:DocumentEdition de WITH de.DocumentID = d.DocumentID
            WHERE de.EditionCode = 'SV'
              AND pp.YearEdition = '2017'
              AND de.CountryEditionCode = 'RUS'
            ORDER BY prod.ProductID DESC, pp.YearEdition DESC
        ")->getResult();

        $extra = array('single-element');
        foreach ($extraRaw as $raw) {
            $extra[] = $raw['productpicture'];
        }

        $picturesRaw = $this->_em->createQuery("
            SELECT pict.filename path, prod.ProductID
            FROM VidalDrugBundle:Picture pict
            JOIN VidalDrugBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
            JOIN VidalDrugBundle:Product prod WITH pp.ProductID = prod.ProductID
            WHERE prod.ProductID IN (:productIds)
                AND pp.CountryEditionCode = 'RUS'
                AND (pp.YearEdition = 2017 OR prod.ProductTypeCode = 'BAD')
                AND (pp.productpicture IN (:extra) OR (pp.DateEdit IS NOT NULL AND pp.DateEditFormatted > '2016-04-01'))
                AND pp.found = TRUE
            ORDER BY prod.ProductID DESC, pp.YearEdition DESC
        ")->setParameter('productIds', $productIds)
            ->setParameter('extra', $extra)
            ->getResult();

        if (empty($picturesRaw)) {
            $picturesRaw = $this->_em->createQuery("
                SELECT pict.filename path, prod.ProductID
                FROM VidalDrugBundle:Picture pict
                JOIN VidalDrugBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
                JOIN VidalDrugBundle:Product prod WITH pp.ProductID = prod.ProductID
                WHERE prod.ProductID IN (:productIds)
                    AND pp.CountryEditionCode = 'RUS'
                    AND (pp.YearEdition = 2016 OR prod.ProductTypeCode = 'BAD')
                    AND (pp.productpicture IN (:extra) OR (pp.DateEdit IS NOT NULL AND pp.DateEditFormatted > '2016-04-01'))
                    AND pp.found = TRUE
                ORDER BY prod.ProductID DESC, pp.YearEdition DESC
            ")->setParameter('productIds', $productIds)
                ->setParameter('extra', $extra)
                ->getResult();
        }

        $pictures = array();

        for ($i = 0; $i < count($picturesRaw); $i++) {
            $pictures[] = $picturesRaw[$i]['path'];
        }

        return array_unique($pictures);
    }

    public function findIdsByProduct($productId)
    {
        $raw = $this->_em->createQuery("
            SELECT pict.PictureID, pict.PathForElectronicEdition path, pp.YearEdition
            FROM VidalDrugBundle:Picture pict
            JOIN VidalDrugBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
            WHERE pp.ProductID = :ProductID AND pp.YearEdition IN (2016,2017)
        ")->setParameter('ProductID', $productId)
            ->getResult();

        $ids = array();
        foreach ($raw as $r) {
            $path = preg_replace('/.+\\\\JPG\\\\/', '', $r['path']);
            $ids[] = $r['PictureID'] . ':' . $path . ':' . $r['YearEdition'];
        }

        return $ids;
    }

    public function findByProductIds($productIds, $year = null)
    {
        # эти вне правила - условие от Максимилиана
        $extraRaw = $this->_em->createQuery("
            SELECT pp.productpicture
            FROM VidalDrugBundle:Picture pict
            JOIN VidalDrugBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
            JOIN VidalDrugBundle:Product prod WITH pp.ProductID = prod.ProductID
            JOIN prod.document d
            JOIN VidalDrugBundle:DocumentEdition de WITH de.DocumentID = d.DocumentID
            WHERE de.EditionCode = 'SV'
              AND pp.YearEdition = '2017'
              AND de.CountryEditionCode = 'RUS'
            ORDER BY prod.ProductID DESC, pp.YearEdition DESC
        ")->getResult();

        $extra = array('single-element');
        foreach ($extraRaw as $raw) {
            $extra[] = $raw['productpicture'];
        }

        $pictures2017 = $this->_em->createQuery("
            SELECT pict.filename path, prod.ProductID
            FROM VidalDrugBundle:Picture pict
            JOIN VidalDrugBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
            JOIN VidalDrugBundle:Product prod WITH pp.ProductID = prod.ProductID
            WHERE prod.ProductID IN (:productIds)
                AND pp.CountryEditionCode = 'RUS'
                AND (pp.YearEdition = 2017 OR prod.ProductTypeCode = 'BAD')
                AND (pp.productpicture IN (:extra) OR (pp.DateEdit IS NOT NULL AND pp.DateEditFormatted > '2016-04-01'))
                AND pp.found = TRUE
            ORDER BY prod.ProductID DESC, pp.YearEdition DESC
        ")->setParameter('productIds', $productIds)
            ->setParameter('extra', $extra)
            ->getResult();

        $picturesRaw = $this->_em->createQuery("
            SELECT pict.filename path, prod.ProductID
            FROM VidalDrugBundle:Picture pict
            JOIN VidalDrugBundle:ProductPicture pp WITH pp.PictureID = pict.PictureID
            JOIN VidalDrugBundle:Product prod WITH pp.ProductID = prod.ProductID
            WHERE prod.ProductID IN (:productIds)
                AND pp.CountryEditionCode = 'RUS'
                AND (pp.YearEdition = 2016 OR prod.ProductTypeCode = 'BAD')
                AND (pp.productpicture IN (:extra) OR (pp.DateEdit IS NOT NULL AND pp.DateEditFormatted > '2016-04-01'))
                AND pp.found = TRUE
            ORDER BY prod.ProductID DESC, pp.YearEdition DESC
        ")->setParameter('productIds', $productIds)
            ->setParameter('extra', $extra)
            ->getResult();

        $pictures = array();

        for ($i = 0; $i < count($picturesRaw); $i++) {
            $key = $picturesRaw[$i]['ProductID'];
            if (!isset($pictures[$key])) {
                $pictures[$key] = $picturesRaw[$i]['path'];
            }
        }

        for ($i = 0; $i < count($pictures2017); $i++) {
            $key = $pictures2017[$i]['ProductID'];
            $pictures[$key] = $pictures2017[$i]['path'];
        }

        $products = $this->_em->createQuery('
			SELECT p.ProductID, p.photo, p.photo2, p.photo3, p.photo4, p.photo5, p.photo6
			FROM VidalDrugBundle:Product p
			WHERE p.ProductID IN (:productIds) AND (p.photo IS NOT NULL OR p.photo2 IS NOT NULL OR p.photo3 IS NOT NULL OR p.photo4 IS NOT NULL OR p.photo5 IS NOT NULL OR p.photo6 IS NOT NULL)
		')->setParameter('productIds', $productIds)
            ->getResult();

        foreach ($products as $product) {
            $key = $product['ProductID'];

            if ($product['photo']) {
                $pictures[$key] = $product['photo'];
            }
            elseif ($product['photo2']) {
                $pictures[$key] = $product['photo2'];
            }
            elseif ($product['photo3']) {
                $pictures[$key] = $product['photo3'];
            }
            elseif ($product['photo4']) {
                $pictures[$key] = $product['photo4'];
            }
            elseif ($product['photo5']) {
                $pictures[$key] = $product['photo5'];
            }
            elseif ($product['photo6']) {
                $pictures[$key] = $product['photo6'];
            }
        }

        return $pictures;
    }

    public function findByInfoPageID($InfoPageID)
    {
        $picture = $this->_em->createQuery('
			SELECT p.PathForElectronicEdition path
			FROM VidalDrugBundle:Picture p
			JOIN p.infoPages i WITH i = :InfoPageID
		')->setParameter('InfoPageID', $InfoPageID)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!empty($picture)) {
            $picture['path'] = strtolower(preg_replace('/.+\\\\JPG\\\\/', '', $picture['path']));
        }

        return $picture;
    }
}