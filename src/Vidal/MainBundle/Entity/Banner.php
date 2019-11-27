<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="BannerRepository")
 * @ORM\Table(name="banner")
 * @Filestore\Uploadable
 */
class Banner extends BaseEntity
{
    const GROUP_RIGHT = 10;
    const GROUP_LEFT = 7;
    const GROUP_TOP = 2;
    const GROUP_BOTTOM = 1;
    const GROUP_PRODUCT = 11;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Filestore\UploadableField(mapping="banner")
     */
    protected $banner;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Filestore\UploadableField(mapping="banner")
     */
    protected $mobileBanner;

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank(message="Укажите идентификатор баннера для Google Analitics)")
     */
    protected $title;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $titleMobile;

    /**
     * @ORM\Column(length=500)
     * @Assert\NotBlank(message="Укажите ссылку для баннера")
     * @Assert\Url(message = "Введен невалидный URL-адрес")
     */
    protected $link;

    /**
     * @ORM\Column(length=500, nullable=true)
     */
    protected $linkMobile;

    /**
     * @ORM\Column(length=500, nullable=true)
     * @Assert\Url(message = "Введен невалидный URL-адрес")
     */
    protected $loggedLink;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $displayed;

    /**
     * @ORM\Column(type="integer")
     */
    protected $clicks;

    /** @ORM\ManyToOne(targetEntity="BannerGroup", inversedBy="banners") */
    protected $group;

    /** @ORM\OneToMany(targetEntity="BannerClick", mappedBy="banner") */
    protected $bannerClicks;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $width;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $height;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $mobileWidth;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $mobileHeight;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $position = 0;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $mobilePosition = 0;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $mobileProductPosition = 0;

    /** @ORM\Column(type="boolean") */
    protected $mobileProduct = false;

    /** @ORM\Column(type="boolean") */
    protected $mobile = true;

    /** @ORM\Column(type="boolean") */
    protected $testMode = false;

    /** @ORM\Column(type="boolean") */
    protected $indexPage = false;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $clickEvent;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $showEvent;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $displayTo;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $alt;

    /** @ORM\Column(type="text", nullable=true) */
    protected $forPage;

    /** @ORM\Column(type="text", nullable=true) */
    protected $htmlBanner;

    /** @ORM\Column(type="text", nullable=true) */
    protected $mobileHtmlBanner;

    /** @ORM\Column(type="text", nullable=true) */
    protected $notForPage;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Assert\Url(message = "Введен невалидный URL-адрес")
     */
    protected $trackImageUrl;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Assert\Url(message = "Введен невалидный URL-адрес")
     */
    protected $trackImageUrlMobile;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $rotateWithId;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $rotateWithPosition;

    /**
     * @ORM\Column(type = "datetime", nullable=true)
     */
    protected $expired;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $maxClicks;

    /** @ORM\Column(type="boolean") */
    protected $mobileRotateOnly = false;

    /** @ORM\Column(type="boolean") */
    protected $mustShow = false;

    /** @ORM\Column(type="boolean") */
    protected $specOnly = false;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $atc;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $nosology;

    /** @ORM\Column(type="text", nullable=true) */
    protected $productIds;

    /** @ORM\Column(type="text", nullable=true) */
    protected $atcCodes;

    /** @ORM\Column(type="text", nullable=true) */
    protected $nosologyCodes;

    /** @ORM\Column(type="text", nullable=true) */
    protected $nosologyProductIds;

    /** @ORM\Column(type="boolean") */
    protected $opened = false;

    /** @ORM\Column(type="text", nullable=true) */
    protected $products;

    /** @ORM\Column(type="boolean") */
    protected $topPriority = false;

    public function __construct()
    {
        $this->clicks = 0;
        $this->displayed = 0;
        $this->bannerClicks = new ArrayCollection();
    }

    public function __toString()
    {
        if (!empty($this->link)) {
            return '[' . $this->id . '] ' . $this->link;
        }
        elseif ($this->id) {
            return '[' . $this->id . ']';
        }
        else {
            return '';
        }
    }

    /**
     * @param mixed $banner
     */
    public function setBanner($banner)
    {
        $this->banner = $banner;
    }

    /**
     * @return mixed
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * @param mixed $clicks
     */
    public function setClicks($clicks)
    {
        $this->clicks = $clicks;
    }

    /**
     * @return mixed
     */
    public function getClicks()
    {
        return $this->clicks;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Получение пути хранения изображения баннера
     *
     * @return null|string
     */
    public function getPath()
    {
        return empty($this->banner['path']) ? null : $this->banner['path'];
    }

    /**
     * @param mixed $displayed
     */
    public function setDisplayed($displayed)
    {
        $this->displayed = $displayed;
    }

    /**
     * @return mixed
     */
    public function getDisplayed()
    {
        return $this->displayed;
    }

    public function isSwf()
    {
        $ext = pathinfo($this->banner['path'], PATHINFO_EXTENSION);

        return $ext == 'swf' || $ext == 'fla';
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getLoggedLink()
    {
        return $this->loggedLink;
    }

    /**
     * @param mixed $loggedLink
     */
    public function setLoggedLink($loggedLink)
    {
        $this->loggedLink = $loggedLink;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getMobilePosition()
    {
        return $this->mobilePosition;
    }

    /**
     * @param mixed $mobilePosition
     */
    public function setMobilePosition($mobilePosition)
    {
        $this->mobilePosition = $mobilePosition;
    }

    /**
     * @return boolean
     */
    public function isMobile()
    {
        return $this->mobile;
    }

    /**
     * @param boolean $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getClickEvent()
    {
        return $this->clickEvent;
    }

    /**
     * @param mixed $clickEvent
     */
    public function setClickEvent($clickEvent)
    {
        $this->clickEvent = $clickEvent;
    }

    /**
     * @return mixed
     */
    public function getShowEvent()
    {
        return $this->showEvent;
    }

    /**
     * @param mixed $showEvent
     */
    public function setShowEvent($showEvent)
    {
        $this->showEvent = $showEvent;
    }

    /**
     * @return mixed
     */
    public function getIndexPage()
    {
        return $this->indexPage;
    }

    /**
     * @param mixed $indexPage
     */
    public function setIndexPage($indexPage)
    {
        $this->indexPage = $indexPage;
    }

    /**
     * @return mixed
     */
    public function getDisplayTo()
    {
        return $this->displayTo;
    }

    /**
     * @param mixed $displayTo
     */
    public function setDisplayTo($displayTo)
    {
        $this->displayTo = $displayTo;
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param mixed $alt
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
    }

    /**
     * @return mixed
     */
    public function getMobileBanner()
    {
        return $this->mobileBanner;
    }

    /**
     * @param mixed $mobileBanner
     */
    public function setMobileBanner($mobileBanner)
    {
        $this->mobileBanner = $mobileBanner;
    }

    /**
     * @return mixed
     */
    public function getMobileWidth()
    {
        return $this->mobileWidth;
    }

    /**
     * @param mixed $mobileWidth
     */
    public function setMobileWidth($mobileWidth)
    {
        $this->mobileWidth = $mobileWidth;
    }

    /**
     * @return mixed
     */
    public function getMobileHeight()
    {
        return $this->mobileHeight;
    }

    /**
     * @param mixed $mobileHeight
     */
    public function setMobileHeight($mobileHeight)
    {
        $this->mobileHeight = $mobileHeight;
    }

    /**
     * @return mixed
     */
    public function getMobileProduct()
    {
        return $this->mobileProduct;
    }

    /**
     * @param mixed $mobileProduct
     */
    public function setMobileProduct($mobileProduct)
    {
        $this->mobileProduct = $mobileProduct;
    }

    /**
     * @return mixed
     */
    public function getForPage()
    {
        return $this->forPage;
    }

    /**
     * @param mixed $forPage
     */
    public function setForPage($forPage)
    {
        $this->forPage = $forPage;
    }

    /**
     * @return mixed
     */
    public function getNotForPage()
    {
        return $this->notForPage;
    }

    /**
     * @param mixed $notForPage
     */
    public function setNotForPage($notForPage)
    {
        $this->notForPage = $notForPage;
    }

    /**
     * @return mixed
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param mixed $testMode
     */
    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
    }

    /**
     * @return mixed
     */
    public function getMobileProductPosition()
    {
        return $this->mobileProductPosition;
    }

    /**
     * @param mixed $mobileProductPosition
     */
    public function setMobileProductPosition($mobileProductPosition)
    {
        $this->mobileProductPosition = $mobileProductPosition;
    }

    /**
     * @return mixed
     */
    public function getTrackImageUrl()
    {
        return $this->trackImageUrl;
    }

    /**
     * @param mixed $trackImageUrl
     */
    public function setTrackImageUrl($trackImageUrl)
    {
        $this->trackImageUrl = $trackImageUrl;
    }

    /**
     * @return mixed
     */
    public function getRotateWithId()
    {
        return $this->rotateWithId;
    }

    /**
     * @param mixed $rotateWithId
     */
    public function setRotateWithId($rotateWithId)
    {
        $this->rotateWithId = $rotateWithId;
    }

    /**
     * @return mixed
     */
    public function getTitleMobile()
    {
        return $this->titleMobile;
    }

    /**
     * @param mixed $titleMobile
     */
    public function setTitleMobile($titleMobile)
    {
        $this->titleMobile = $titleMobile;
    }

    /**
     * @return mixed
     */
    public function getLinkMobile()
    {
        return $this->linkMobile;
    }

    /**
     * @param mixed $linkMobile
     */
    public function setLinkMobile($linkMobile)
    {
        $this->linkMobile = $linkMobile;
    }

    /**
     * @return mixed
     */
    public function getTrackImageUrlMobile()
    {
        return $this->trackImageUrlMobile;
    }

    /**
     * @param mixed $trackImageUrlMobile
     */
    public function setTrackImageUrlMobile($trackImageUrlMobile)
    {
        $this->trackImageUrlMobile = $trackImageUrlMobile;
    }

    /**
     * @return mixed
     */
    public function getExpired()
    {
        return $this->expired;
    }

    /**
     * @param mixed $expired
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;
    }

    /**
     * @return mixed
     */
    public function getMaxClicks()
    {
        return $this->maxClicks;
    }

    /**
     * @param mixed $maxClicks
     */
    public function setMaxClicks($maxClicks)
    {
        $this->maxClicks = $maxClicks;
    }

    /**
     * @return mixed
     */
    public function getMobileRotateOnly()
    {
        return $this->mobileRotateOnly;
    }

    /**
     * @param mixed $mobileRotateOnly
     */
    public function setMobileRotateOnly($mobileRotateOnly)
    {
        $this->mobileRotateOnly = $mobileRotateOnly;
    }

    /**
     * @return mixed
     */
    public function getMustShow()
    {
        return $this->mustShow;
    }

    /**
     * @param mixed $mustShow
     */
    public function setMustShow($mustShow)
    {
        $this->mustShow = $mustShow;
    }

    /**
     * @return mixed
     */
    public function getBannerClicks()
    {
        return $this->bannerClicks;
    }

    /**
     * @param mixed $bannerClicks
     */
    public function setBannerClicks($bannerClicks)
    {
        $this->bannerClicks = $bannerClicks;
    }

    /**
     * @return mixed
     */
    public function getSpecOnly()
    {
        return $this->specOnly;
    }

    /**
     * @param mixed $specOnly
     */
    public function setSpecOnly($specOnly)
    {
        $this->specOnly = $specOnly;
    }

    /**
     * @return mixed
     */
    public function getAtc()
    {
        return $this->atc;
    }

    /**
     * @param mixed $atc
     */
    public function setAtc($atc)
    {
        $this->atc = $atc;
    }

    /**
     * @return mixed
     */
    public function getProductIds()
    {
        return $this->productIds;
    }

    /**
     * @param mixed $productIds
     */
    public function setProductIds($productIds)
    {
        $this->productIds = $productIds;
    }

    /**
     * @return mixed
     */
    public function getNosology()
    {
        return $this->nosology;
    }

    /**
     * @param mixed $nosology
     */
    public function setNosology($nosology)
    {
        $this->nosology = $nosology;
    }

    /**
     * @return mixed
     */
    public function getAtcCodes()
    {
        return $this->atcCodes;
    }

    /**
     * @param mixed $atcCodes
     */
    public function setAtcCodes($atcCodes)
    {
        $this->atcCodes = $atcCodes;
    }

    /**
     * @return mixed
     */
    public function getNosologyCodes()
    {
        return $this->nosologyCodes;
    }

    /**
     * @param mixed $nosologyCodes
     */
    public function setNosologyCodes($nosologyCodes)
    {
        $this->nosologyCodes = $nosologyCodes;
    }

    /**
     * @return mixed
     */
    public function getNosologyProductIds()
    {
        return $this->nosologyProductIds;
    }

    /**
     * @param mixed $nosologyProductIds
     */
    public function setNosologyProductIds($nosologyProductIds)
    {
        $this->nosologyProductIds = $nosologyProductIds;
    }

    /**
     * @return mixed
     */
    public function getOpened()
    {
        return $this->opened;
    }

    /**
     * @param mixed $opened
     */
    public function setOpened($opened)
    {
        $this->opened = $opened;
    }

    /**
     * @return mixed
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param mixed $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @return mixed
     */
    public function getRotateWithPosition()
    {
        return $this->rotateWithPosition;
    }

    /**
     * @param mixed $rotateWithPosition
     */
    public function setRotateWithPosition($rotateWithPosition)
    {
        $this->rotateWithPosition = $rotateWithPosition;
    }

    /**
     * @return mixed
     */
    public function getTopPriority()
    {
        return $this->topPriority;
    }

    /**
     * @param mixed $topPriority
     */
    public function setTopPriority($topPriority)
    {
        $this->topPriority = $topPriority;
    }

    /**
     * @return mixed
     */
    public function getHtmlBanner()
    {
        return $this->htmlBanner;
    }

    /**
     * @param string $htmlBanner
     */
    public function setHtmlBanner($htmlBanner)
    {
        $this->htmlBanner = $htmlBanner;
    }

    /**
     * @return mixed
     */
    public function getMobileHtmlBanner()
    {
        return $this->mobileHtmlBanner;
    }

    /**
     * @param mixed $mobileHtmlBanner
     */
    public function setMobileHtmlBanner($mobileHtmlBanner)
    {
        $this->mobileHtmlBanner = $mobileHtmlBanner;
    }
}