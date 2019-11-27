<?php

namespace Vidal\BigMamaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @inheritdoc
 * @ORM\Entity(repositoryClass="SpecialistRepository")
 * @ORM\Table(name="big_mama_specialist")
 */
class Specialist extends BaseMediaEntity
{
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
}