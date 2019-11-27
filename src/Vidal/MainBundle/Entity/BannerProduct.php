<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="banner_product")
 */
class BannerProduct extends BaseEntity
{
    /** @ORM\ManyToOne(targetEntity="Banner", inversedBy="bannerProducts") */
    protected $banner;

    /** @ORM\Column(type = "integer") */
    protected $ProductID;

    /**
     * @return mixed
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param mixed $banner
     */
    public function setBanner(Banner $banner)
    {
        $this->banner = $banner;
    }

    /**
     * @return mixed
     */
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param mixed $ProductID
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
    }
}