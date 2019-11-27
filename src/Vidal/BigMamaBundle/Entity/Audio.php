<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @inheritdoc
 * @ORM\Entity(repositoryClass="AudioRepository")
 * @ORM\Table(name="big_mama_audio")
 */
class Audio extends BaseMediaEntity
{
    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="big_mama")
     * @Assert\File(
     *        maxSize="100M",
     *        maxSizeMessage="Аудио не может быть больше 100Мб",
     *        mimeTypesMessage="Аудио должно быть в формате .mp3"
     * )
     */
    protected $audio;

    /** @ORM\ManyToOne(targetEntity="Category", inversedBy="audios") */
    protected $category;

    /**
     * @return mixed
     */
    public function getAudio()
    {
        return $this->audio;
    }

    /**
     * @param mixed $audio
     */
    public function setAudio($audio)
    {
        $this->audio = $audio;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}