<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DrugInfoRepository extends EntityRepository
{
    public function createProducts($products)
    {
        $infos = $this->_em->createQuery("
            SELECT i.entityId as ProductID
            FROM VidalMainBundle:DrugInfo i
            WHERE i.entityClass = 'Product'
            ORDER BY i.entityId
        ")->getResult();

        $ids = array();
        if (!empty($infos)) {
            foreach ($infos as $info) {
                $ids[] = $info['ProductID'];
            }
        }

        foreach ($products as $p) {
            if (false == in_array($p['ProductID'], $ids)) {
                $entity = new DrugInfo();
                $entity->setEntityClass('Product');
                $entity->setEntityId($p['ProductID']);
                $this->_em->persist($entity);
                $this->_em->flush($entity);
            }
        }
    }

    public function getProductPageviews()
    {
        $raw = $this->_em->createQuery("
            SELECT i.entityId as ProductID, i.ga_pageviews
            FROM VidalMainBundle:DrugInfo i
            WHERE i.entityClass = 'Product' AND i.ga_pageviews > 0
            ORDER BY i.entityId
        ")->getResult();

        $results = array();
        for ($i = 0; $i < count($raw); $i++) {
            $key = $raw[$i]['ProductID'] . '';
            $results[$key] = $raw[$i]['ga_pageviews'];
        }

        return $results;
    }

    public function getProducts()
    {
        return $this->_em->createQuery("
            SELECT i.entityId as ProductID, i.ga_pageviews, i.uri
            FROM VidalMainBundle:DrugInfo i
            WHERE i.entityClass = 'Product'
            ORDER BY i.entityId
        ")->getResult();
    }

    public function findProductIdsWithUri()
    {
        $infos = $this->_em->createQuery("
            SELECT i.entityId as ProductID
            FROM VidalMainBundle:DrugInfo i
            WHERE i.entityClass = 'Product' AND i.uri IS NOT NULL
            ORDER BY i.entityId
        ")->getResult();

        $ids = array();
        if (!empty($infos)) {
            foreach ($infos as $info) {
                $ids[] = $info['ProductID'];
            }
        }

        return $ids;
    }

    public function updateProductUri($ProductID, $uri)
    {
        $this->createProducts(array(array('ProductID' =>$ProductID)));

        $this->_em->createQuery("
            UPDATE VidalMainBundle:DrugInfo i
            SET i.uri = :uri
            WHERE i.entityClass = 'Product' AND i.entityId = :ProductID
        ")->setParameter('uri', $uri)->setParameter('ProductID', $ProductID)->execute();
    }

    public function findProductID($uri)
    {
        $info = $this->_em->createQuery("
            SELECT i.entityId as ProductID
            FROM VidalMainBundle:DrugInfo i
            WHERE i.entityClass = 'Product' AND i.uri = :uri
        ")->setParameter('uri', $uri)->getOneOrNullResult();

        return $info == null ?false: $info['ProductID'];
    }
}