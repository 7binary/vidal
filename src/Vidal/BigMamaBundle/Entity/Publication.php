<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @inheritdoc
 * @ORM\Entity(repositoryClass="PublicationRepository")
 * @ORM\Table(name="big_mama_publication")
 */
class Publication extends BaseMediaEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="publications")
     * @Assert\NotBlank(message="Пожалуйста, укажите раздел")
     */
    protected $category;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Пожалуйста, укажите заголовок")
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Пожалуйста, укажите содержимое")
     */
    protected $body;

    public function __toString()
    {
        return empty($this->title) ? '' : $this->title;
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