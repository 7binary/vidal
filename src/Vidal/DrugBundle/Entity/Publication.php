<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="PublicationRepository") @ORM\Table(name="publication") @FileStore\Uploadable */
class Publication extends BaseEntity
{
    const PROJECT_VETERINARY = 'veterinary';

    public static function getProjectOptions()
    {
        return array(
            null => 'Обычные новости',
            self::PROJECT_VETERINARY => 'Ветеринария',
        );
    }

    /** @ORM\ManyToMany(targetEntity="Vidal\DrugBundle\Entity\PublicationCategory", mappedBy="publications") */
    protected $categories;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="photo")
     * @Assert\Image(
     *        maxSize="4M",
     *    maxSizeMessage="Принимаются фотографии размером до 4 Мб"
     * )
     */
    protected $photo;

    /** @ORM\Column(length=500) */
    protected $title;

    /** @ORM\Column(type="text", nullable=true) */
    protected $announce;

    /** @ORM\Column(type="text") */
    protected $body;

    /** @ORM\Column(type="text", nullable=true) */
    protected $bodyMobile;

    /** @ORM\Column(type="text", nullable=true) */
    protected $bodyLinked;

    /** @ORM\Column(length=255, nullable=true) */
    protected $keyword;

    /** @ORM\Column(length=500, nullable=true) */
    protected $link;

    /** @ORM\Column(length=500, nullable=true) */
    protected $linkManual;

    /** @ORM\Column(length=255, nullable=true) */
    protected $project;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $date;

    /**
     * @ORM\ManyToMany(targetEntity="Nozology", inversedBy="publications")
     * @ORM\JoinTable(name="publication_n",
     *        joinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="NozologyCode", referencedColumnName="NozologyCode")})
     */
    protected $nozologies;

    /**
     * @ORM\ManyToMany(targetEntity="Molecule", inversedBy="publications")
     * @ORM\JoinTable(name="publication_molecule",
     *        joinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="MoleculeID", referencedColumnName="MoleculeID")})
     */
    protected $molecules;

    /**
     * @ORM\ManyToMany(targetEntity="Document", inversedBy="publications")
     * @ORM\JoinTable(name="publication_document",
     *        joinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="DocumentID", referencedColumnName="DocumentID")})
     */
    protected $documents;

    /**
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="publications")
     * @ORM\JoinTable(name="publication_product",
     *        joinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ProductID", referencedColumnName="ProductID")})
     */
    protected $products;

    /**
     * @ORM\ManyToMany(targetEntity="ATC", inversedBy="publications")
     * @ORM\JoinTable(name="publication_atc",
     *        joinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="ATCCode", referencedColumnName="ATCCode")})
     */
    protected $atcCodes;

    /**
     * @ORM\ManyToMany(targetEntity="InfoPage", inversedBy="publications")
     * @ORM\JoinTable(name="publication_infopage",
     *        joinColumns={@ORM\JoinColumn(name="publication_id", referencedColumnName="id")},
     *        inverseJoinColumns={@ORM\JoinColumn(name="InfoPageID", referencedColumnName="InfoPageID")})
     */
    protected $infoPages;

    /** @ORM\ManyToMany(targetEntity="Tag", inversedBy="publications") */
    protected $tags;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="video")
     * @Assert\File(
     *        maxSize="100M",
     *        maxSizeMessage="Видео не может быть больше 100Мб",
     *        mimeTypesMessage="Видео должно быть в формате .flv"
     * )
     */
    protected $video;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $videoWidth;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $videoHeight;

    /** @ORM\Column(length=10, nullable=true) */
    protected $hidden;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $priority;

    /** @ORM\Column(type="text", nullable=true) */
    protected $code;

    /** @ORM\Column(type="boolean") */
    protected $testMode = false;

    /** @ORM\Column(type="boolean") */
    protected $sticked = false;

    /** @ORM\ManyToMany(targetEntity="Video", inversedBy="publications", cascade={"persist"}) */
    protected $videos;

    /** @ORM\Column(type="boolean") */
    protected $mobile = false;

    /** @ORM\Column(type="boolean") */
    protected $push = false;

    /** @ORM\Column(type="boolean") */
    protected $pushNeuro = false;

    /** @ORM\Column(type="boolean") */
    protected $pushVeterinary = false;

    /** @ORM\Column(type="boolean") */
    protected $invisible = false;

    /** @ORM\OneToMany(targetEntity="Push", mappedBy="publication") */
    protected $pushes;

    /** @ORM\Column(type="boolean") */
    protected $disableLinks = false;

    public function __construct()
    {
        $this->pushes = new ArrayCollection();

        $now = new \DateTime('now');
        $this->created = $now;
        $this->updated = $now;
        $this->date = $now;
        $this->nozologies = new ArrayCollection();
        $this->molecules = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->atcCodes = new ArrayCollection();
        $this->infoPages = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function __toString()
    {
        return empty($this->title) ? '' : $this->title;
    }

    /**
     * @param mixed $announce
     */
    public function setAnnounce($announce)
    {
        $this->announce = $announce;
    }

    /**
     * @return mixed
     */
    public function getAnnounce()
    {
        return $this->announce;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $bodyMobile
     */
    public function setBodyMobile($bodyMobile)
    {
        $this->bodyMobile = $bodyMobile;
    }

    /**
     * @return mixed
     */
    public function getBodyMobile()
    {
        return $this->bodyMobile;
    }

    /**
     * @return mixed
     */
    public function getBodyForMobileApi()
    {
        if($this->getBodyMobile()) {
            return $this->getBodyMobile();
        }

        return $this->getBody();
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $title = str_replace('<p>', '', $title);
        $title = str_replace('</p>', '', $title);
        $title = str_replace('<div>', '', $title);
        $title = str_replace('</div>', '', $title);

        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
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
    public function getAtcCodes()
    {
        return $this->atcCodes;
    }

    /**
     * @param mixed $documents
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param mixed $infoPages
     */
    public function setInfoPages($infoPages)
    {
        $this->infoPages = $infoPages;
    }

    /**
     * @return mixed
     */
    public function getInfoPages()
    {
        return $this->infoPages;
    }

    /**
     * @param mixed $molecules
     */
    public function setMolecules($molecules)
    {
        $this->molecules = $molecules;
    }

    /**
     * @return mixed
     */
    public function getMolecules()
    {
        return $this->molecules;
    }

    /**
     * @param mixed $nozologies
     */
    public function setNozologies($nozologies)
    {
        $this->nozologies = $nozologies;
    }

    /**
     * @return mixed
     */
    public function getNozologies()
    {
        return $this->nozologies;
    }

    public function addDocument(Document $document)
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
        }

        return $this;
    }

    public function removeDocument(Document $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function addTag(Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag($tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @param mixed $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

    /**
     * @return mixed
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param mixed $videoHeight
     */
    public function setVideoHeight($videoHeight)
    {
        $this->videoHeight = $videoHeight;
    }

    /**
     * @return mixed
     */
    public function getVideoHeight()
    {
        return $this->videoHeight;
    }

    /**
     * @param mixed $videoWidth
     */
    public function setVideoWidth($videoWidth)
    {
        $this->videoWidth = $videoWidth;
    }

    /**
     * @return mixed
     */
    public function getVideoWidth()
    {
        return $this->videoWidth;
    }

    /**
     * @param mixed $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return mixed
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    public function getT()
    {
        return 'Publication';
    }

    public function addProduct(Product $product)
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }
    }

    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);
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
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
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
    public function getTestMode()
    {
        return $this->testMode;
    }

    public function addAtcCode($atcCode)
    {
        $this->atcCodes[] = $atcCode;

        return $this;
    }

    public function removeAtcCode($atcCode)
    {
        $this->atcCodes->removeElement($atcCode);
    }

    public function addNozology($nozology)
    {
        $this->nozologies[] = $nozology;

        return $this;
    }

    public function removeNozology($nozology)
    {
        $this->nozologies->removeElement($nozology);

        return $this;
    }

    public function addMolecule($molecule)
    {
        $this->molecules[] = $molecule;

        return $this;
    }

    public function removeMolecule($molecule)
    {
        $this->molecules->removeElement($molecule);

        return $this;
    }

    public function addInfoPage($infoPage)
    {
        $this->infoPages[] = $infoPage;

        return $this;
    }

    public function removeInfoPage($infoPage)
    {
        $this->infoPages->removeElement($infoPage);

        return $this;
    }

    /**
     * @param mixed $videos
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;
    }

    /**
     * @return mixed
     */
    public function getVideos()
    {
        return $this->videos;
    }

    public function addVideo(Video $video)
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
        }

        return $this;
    }

    public function removeVideo(Video $video)
    {
        $this->videos->removeElement($video);
    }

    /**
     * @return mixed
     */
    public function getSticked()
    {
        return $this->sticked;
    }

    /**
     * @param mixed $sticked
     */
    public function setSticked($sticked)
    {
        $this->sticked = $sticked;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getPush()
    {
        return $this->push;
    }

    /**
     * @param mixed $push
     */
    public function setPush($push)
    {
        $this->push = $push;
    }

    /**
     * @return mixed
     */
    public function getInvisible()
    {
        return $this->invisible;
    }

    /**
     * @param mixed $invisible
     */
    public function setInvisible($invisible)
    {
        $this->invisible = $invisible;
    }

    /**
     * @return mixed
     */
    public function getPushes()
    {
        return $this->pushes;
    }

    /**
     * @param mixed $pushes
     */
    public function setPushes($pushes)
    {
        $this->pushes = $pushes;
    }

    /**
     * @return mixed
     */
    public function getPushNeuro()
    {
        return $this->pushNeuro;
    }

    /**
     * @param mixed $pushNeuro
     */
    public function setPushNeuro($pushNeuro)
    {
        $this->pushNeuro = $pushNeuro;
    }

    /**
     * @return mixed
     */
    public function getDisableLinks()
    {
        return $this->disableLinks;
    }

    /**
     * @param mixed $disableLinks
     */
    public function setDisableLinks($disableLinks)
    {
        $this->disableLinks = $disableLinks;
    }

    /**
     * @return mixed
     */
    public function getBodyLinked()
    {
        return $this->bodyLinked;
    }

    /**
     * @param mixed $bodyLinked
     */
    public function setBodyLinked($bodyLinked)
    {
        $this->bodyLinked = $bodyLinked;
    }

    /**
     * @return mixed
     */
    public function getPushVeterinary()
    {
        return $this->pushVeterinary;
    }

    /**
     * @param mixed $pushVeterinary
     */
    public function setPushVeterinary($pushVeterinary)
    {
        $this->pushVeterinary = $pushVeterinary;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    public function addCategory(PublicationCategory $c)
    {
        $this->categories[] = $c;
        $c->addPublication($this);
    }

    public function removeCategory(PublicationCategory $c)
    {
        $this->categories->removeElement($c);
        $c->removePublication($this);
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
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
    public function getLinkManual()
    {
        return $this->linkManual;
    }

    /**
     * @param mixed $linkManual
     */
    public function setLinkManual($linkManual)
    {
        $this->linkManual = $linkManual;
    }
}