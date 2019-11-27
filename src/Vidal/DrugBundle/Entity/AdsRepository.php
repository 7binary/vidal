<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AdsRepository extends EntityRepository
{
    public function findEnabled()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a')
            ->from('VidalDrugBundle:Ads', 'a')
            ->where('a.enabled = TRUE');

        return $qb->getQuery()->getResult();
    }

    public function findByProduct(Product $product)
    {
        $productId = $product->getProductID();
        $ids = array($productId);

        $products = $this->_em->createQuery("
			SELECT p.ProductID
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE (p.parent = :ProductID OR p.MainID = :ProductID)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('ProductID', $productId)
            ->getResult();

        if (!empty($products)) {
            foreach ($products as $p) {
                $ids[] = $p['ProductID'];
            }
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select('a')
            ->from('VidalDrugBundle:Ads', 'a')
            ->leftJoin('a.products', 'p')
            ->where('a.enabled = TRUE')
            ->andWhere('p.ProductID IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}