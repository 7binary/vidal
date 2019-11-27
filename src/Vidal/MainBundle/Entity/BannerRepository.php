<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Vidal\MainBundle\Controller\BannerController;

class BannerRepository extends EntityRepository
{
    public function countClick($bannerId)
    {
        if ($banner = $this->findOneById($bannerId)) {
            $this->_em->createQuery('
                UPDATE VidalMainBundle:Banner b
                SET b.clicks = b.clicks + 1
                WHERE b = :bannerId
            ')->setParameter('bannerId', $bannerId)->execute();

            $bannerClick = new BannerClick();
            $bannerClick->setBanner($banner);
            $this->_em->persist($bannerClick);
            $this->_em->flush($bannerClick);
        }
    }

    /**
     * @return Banner|null
     */
    public function getBannerMkb($isLogged = false)
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select('b')
            ->leftJoin('b.group', 'g')
            ->andWhere("b.title = 'medznat'")
            ->andWhere('g.enabled = TRUE')
            ->andWhere('b.enabled = TRUE')
            ->orderBy('b.position', 'ASC');

        $now = new \DateTime('now');
        $nowFormatted = $now->format('Y-m-d H:i:s');

        $qb->andWhere("(b.expired IS NULL OR b.expired > :now)")->setParameter('now', $nowFormatted);
        $qb->andWhere("(b.maxClicks IS NULL OR b.maxClicks > b.clicks)");

        if ($isLogged) {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'logged')");
        }
        else {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'guest')");
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findMobile($groupId = null, $isLogged = false, $excludeRotate = true, $ProductID = null, $grouped = false)
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select('b')
            ->leftJoin('b.group', 'g')
            ->andWhere('g.enabled = TRUE')
            ->andWhere('b.enabled = TRUE')
            ->andWhere('b.mobile = TRUE')
            ->orderBy('b.mobilePosition', 'ASC')
            ->addOrderBy('b.id', 'ASC');

        $now = new \DateTime('now');
        $nowFormatted = $now->format('Y-m-d H:i:s');

        if(empty($ProductID)) {
            $qb->andWhere("(b.atc IS NULL OR b.atc = '')");
            $qb->andWhere("(b.nosology IS NULL OR b.nosology = '')");
        } else {
            $qb->andWhere("
                (
                    (b.atc IS NULL OR b.atc = '' OR b.nosology IS NULL OR b.nosology = '') AND 
                    (
                        (b.atc IS NULL OR b.atc = '' OR b.productIds LIKE '%|{$ProductID}|%') AND
                        (b.nosology IS NULL OR b.nosology = '' OR b.nosologyProductIds LIKE '%|{$ProductID}|%')
                    )
                ) OR (
                    (b.atc IS NOT NULL AND b.atc != '' AND b.nosology IS NOT NULL AND b.nosology != '') AND 
                    (
                        (b.productIds LIKE '%|{$ProductID}|%' OR b.nosologyProductIds LIKE '%|{$ProductID}|%')
                    )
                )
            ");
        }

        $qb->andWhere("(b.expired IS NULL OR b.expired > :now)")->setParameter('now', $nowFormatted);
        $qb->andWhere("(b.maxClicks IS NULL OR b.maxClicks > b.clicks)");

        if ($isLogged) {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'logged')");
        }
        else {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'guest')");
        }

        if ($groupId == null) {
            $qb->andWhere('g.id NOT IN (1,2)');
        }
        else {
            $qb->andWhere('g.id = :groupId')->setParameter('groupId', $groupId);
        }

        if (!empty(BannerController::$used)) {
            $qb->andWhere("b.id NOT IN (:used)")->setParameter('used', BannerController::$used);
        }

        if (!empty($ProductID)) {
            $qb->andWhere("(b.products IS NULL OR b.products = '' OR b.products = '{$ProductID}' OR b.products LIKE '%{$ProductID};%')");
        }
        else {
            $qb->andWhere("(b.products IS NULL OR b.products = '')");
        }

        /** @var Banner[] $banners */
        $banners = $qb->getQuery()->getResult();

        foreach ($banners as $banner) {
            if ($banner->getTopPriority() == true) {
                $banners = array($banner);
                break;
            }
        }

        # нужно отсеять баннеры в ротации
        $bannersByKey = array();
        foreach ($banners as $banner) {
            $rotateWithId = $banner->getRotateWithId();
            $rotateWithPosition = $banner->getRotateWithPosition();
            $key = empty($rotateWithId) ? $banner->getId() : $rotateWithId;
            $key = empty($rotateWithPosition) ? $key : $rotateWithPosition;

            if (!isset($bannersByKey[$key])) {
                $bannersByKey[$key] = array();
            }
            $bannersByKey[$key][] = $banner;

            BannerController::$used[] = $banner->getId();
        }

        if ($grouped) {
            return $bannersByKey;
        }

        $banners = array();
        foreach ($bannersByKey as $bannerId => $bannersGroup) {
            if ($excludeRotate) {
                if (count($bannersGroup) == 1) {
                    $banners[] = $bannersGroup[0];
                }
                else {
                    $banners[] = $bannersGroup[array_rand($bannersGroup)];
                }
            }
            else {
                $banners = array_merge($banners, $bannersGroup);
            }
        }

        return $banners;
    }

    public function findMobileProduct($isLogged = false, $excludeRotate = true, $ProductID = null, $grouped = false)
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select('b')
            ->leftJoin('b.group', 'g')
            ->andWhere('g.enabled = TRUE')
            ->andWhere('b.enabled = TRUE')
            ->andWhere('b.mobile = TRUE')
            ->andWhere('b.mobileProduct = TRUE')
            ->orderBy('b.mobileProductPosition', 'ASC');

        $now = new \DateTime('now');
        $nowFormatted = $now->format('Y-m-d H:i:s');

        if(empty($ProductID)) {
            $qb->andWhere("(b.atc IS NULL OR b.atc = '')");
            $qb->andWhere("(b.nosology IS NULL OR b.nosology = '')");
        } else {
            $qb->andWhere("
                (
                    (b.atc IS NULL OR b.atc = '' OR b.nosology IS NULL OR b.nosology = '') AND 
                    (
                        (b.atc IS NULL OR b.atc = '' OR b.productIds LIKE '%|{$ProductID}|%') AND
                        (b.nosology IS NULL OR b.nosology = '' OR b.nosologyProductIds LIKE '%|{$ProductID}|%')
                    )
                ) OR (
                    (b.atc IS NOT NULL AND b.atc != '' AND b.nosology IS NOT NULL AND b.nosology != '') AND 
                    (
                        (b.productIds LIKE '%|{$ProductID}|%' OR b.nosologyProductIds LIKE '%|{$ProductID}|%')
                    )
                )
            ");
        }

        $qb->andWhere("(b.expired IS NULL OR b.expired > :now)")->setParameter('now', $nowFormatted);
        $qb->andWhere("(b.maxClicks IS NULL OR b.maxClicks > b.clicks)");

        if ($isLogged) {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'logged')");
        }
        else {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'guest')");
        }

        if (!empty(BannerController::$used)) {
            $qb->andWhere("b.id NOT IN (:used)")->setParameter('used', BannerController::$used);
        }

        if (!empty($ProductID)) {
            $qb->andWhere("(b.products IS NULL OR b.products = '' OR b.products = '{$ProductID}' OR b.products LIKE '%{$ProductID};%')");
        }
        else {
            $qb->andWhere("(b.products IS NULL OR b.products = '')");
        }

        /** @var Banner[] $banners */
        $banners = $qb->getQuery()->getResult();

        foreach ($banners as $banner) {
            if ($banner->getTopPriority() == true) {
                $banners = array($banner);
                break;
            }
        }

        # нужно отсеять баннеры в ротации
        $bannersByKey = array();
        foreach ($banners as $banner) {
            $rotateWithId = $banner->getRotateWithId();
            $rotateWithPosition = $banner->getRotateWithPosition();
            $key = empty($rotateWithId) ? $banner->getId() : $rotateWithId;
            $key = empty($rotateWithPosition) ? $key : $rotateWithPosition;

            if (!isset($bannersByKey[$key])) {
                $bannersByKey[$key] = array();
            }
            $bannersByKey[$key][] = $banner;

            BannerController::$used[] = $banner->getId();
        }

        if ($grouped) {
            return $bannersByKey;
        }

        $banners = array();
        foreach ($bannersByKey as $bannerId => $bannersGroup) {
            if ($excludeRotate) {
                if (count($bannersGroup) == 1) {
                    $banners[] = $bannersGroup[0];
                }
                else {
                    $banners[] = $bannersGroup[array_rand($bannersGroup)];
                }
            }
            else {
                $banners = array_merge($banners, $bannersGroup);
            }
        }

        return $banners;
    }

    public function findByGroup($groupId, $isLogged = false, $excludeRotate = true, $isMobile = false, $ProductID = null, $grouped = false)
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select('b')
            ->leftJoin('b.group', 'g')
            ->andWhere('g = :groupId')
            ->andWhere('g.enabled = TRUE')
            ->andWhere('b.enabled = TRUE')
            ->setParameter('groupId', $groupId)
            ->orderBy('b.position', 'ASC');

        $now = new \DateTime('now');
        $nowFormatted = $now->format('Y-m-d H:i:s');

        if(empty($ProductID)) {
            $qb->andWhere("(b.atc IS NULL OR b.atc = '')");
            $qb->andWhere("(b.nosology IS NULL OR b.nosology = '')");
        } else {
            $qb->andWhere("
                (
                    (b.atc IS NULL OR b.atc = '' OR b.nosology IS NULL OR b.nosology = '') AND 
                    (
                        (b.atc IS NULL OR b.atc = '' OR b.productIds LIKE '%|{$ProductID}|%') AND
                        (b.nosology IS NULL OR b.nosology = '' OR b.nosologyProductIds LIKE '%|{$ProductID}|%')
                    )
                ) OR (
                    (b.atc IS NOT NULL AND b.atc != '' AND b.nosology IS NOT NULL AND b.nosology != '') AND 
                    (
                        (b.productIds LIKE '%|{$ProductID}|%' OR b.nosologyProductIds LIKE '%|{$ProductID}|%')
                    )
                )
            ");
        }

        $qb->andWhere("(b.expired IS NULL OR b.expired > :now)")->setParameter('now', $nowFormatted);
        $qb->andWhere("(b.maxClicks IS NULL OR b.maxClicks > b.clicks)");

        if ($isLogged) {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'logged')");
        }
        else {
            $qb->andWhere("(b.displayTo IS NULL OR b.displayTo = 'guest')");
        }

        if (!empty(BannerController::$used)) {
            $qb->andWhere("b.id NOT IN (:used)")->setParameter('used', BannerController::$used);
        }

        if (!empty($ProductID)) {
            $qb->andWhere("(b.products IS NULL OR b.products = '' OR b.products = '{$ProductID}' OR b.products LIKE '%{$ProductID};%')");
        }
        else {
            $qb->andWhere("(b.products IS NULL OR b.products = '')");
        }

        /** @var Banner[] $banners */
        $banners = $qb->getQuery()->getResult();

        foreach ($banners as $banner) {
            if ($banner->getTopPriority() == true) {
                $banners = array($banner);
                break;
            }
        }

        # нужно отсеять баннеры в ротации
        $bannersByKey = array();
        foreach ($banners as $banner) {
            $rotateWithId = $banner->getRotateWithId();
            $rotateWithPosition = $banner->getRotateWithPosition();
            $key = empty($rotateWithId) ? $banner->getId() : $rotateWithId;
            $key = empty($rotateWithPosition) ? $key : $rotateWithPosition;

            if (!isset($bannersByKey[$key])) {
                $bannersByKey[$key] = array();
            }
            $bannersByKey[$key][] = $banner;

            BannerController::$used[] = $banner->getId();
        }

        if ($grouped) {
            return $bannersByKey;
        }

        $banners = array();

        foreach ($bannersByKey as $bannerId => $bannersGroup) {
            /** @var Banner[] $bannersGroup */
            if ($excludeRotate) {
                if (count($bannersGroup) == 1) {
                    $banners[] = $bannersGroup[0];
                }
                else {
                    # если НЕ мобильный баннер и выставлен флаг ротации лишь для мобильных баннеров, то берем все
                    if ($isMobile == false) {
                        $continue = false;
                        foreach ($bannersGroup as $banner) {
                            if ($banner->getMobileRotateOnly()) {
                                $continue = true;
                                break;
                            }
                        }
                        if ($continue) {
                            $banners = array_merge($banners, $bannersGroup);
                            continue;
                        }
                    }

                    $banners[] = $bannersGroup[array_rand($bannersGroup)];
                }
            }
            else {
                $banners = array_merge($banners, $bannersGroup);
            }
        }

        return $banners;
    }

    public function findEnabledById($bannerId)
    {
        $qb = $this->createQueryBuilder('b');
        $qb->select('b')
            ->andWhere('b.enabled = TRUE')
            ->andWhere('b.id = :bannerId')
            ->setParameter('bannerId', $bannerId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countShow(Banner $banner)
    {
        $this->_em->createQuery('
            UPDATE VidalMainBundle:Banner b
            SET b.displayed = b.displayed + 1
            WHERE b = :bannerId
        ')->setParameter('bannerId', $banner->getId())
            ->execute();
    }

    public function statsDays($bannerId)
    {
        $pdo = $this->_em->getConnection();

        $stmt = $pdo->prepare("
          SELECT COUNT(id) `value`, DATE_FORMAT(created, '%Y-%m-%d 00:00:00') `date` 
          FROM banner_click 
          WHERE banner_id = :bannerId 
          GROUP BY DATE_FORMAT(created, '%Y-%m-%d')
        ");
        $stmt->bindParam('bannerId', $bannerId);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return $rows;
    }

    public function statsHours($bannerId)
    {
        $pdo = $this->_em->getConnection();

        $stmt = $pdo->prepare("
          SELECT COUNT(id) `value`, DATE_FORMAT(created, '%Y-%m-%d %H:00:00') `date` 
          FROM banner_click 
          WHERE banner_id = :bannerId 
          GROUP BY DATE_FORMAT(created, '%Y-%m-%d %H')
        ");
        $stmt->bindParam('bannerId', $bannerId);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return $rows;
    }

    public function clickTotal($bannerId)
    {
        return $this->_em->createQuery('
            SELECT COUNT(c.id)
            FROM VidalMainBundle:BannerClick c
            WHERE c.banner = :bannerId
        ')->setParameter('bannerId', $bannerId)
            ->getSingleScalarResult();
    }
}