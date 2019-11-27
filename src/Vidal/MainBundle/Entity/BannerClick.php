<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="banner_click")
 */
class BannerClick extends BaseEntity
{
    /** @ORM\ManyToOne(targetEntity="Banner", inversedBy="bannerClicks") */
    protected $banner;

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
}