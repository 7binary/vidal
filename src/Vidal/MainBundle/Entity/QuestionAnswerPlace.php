<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity() @ORM\Table(name="question_answer_place") */
class QuestionAnswerPlace extends BaseEntity
{
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Пожалуйста, укажите название места")
     */
    protected $title;

    /**
     * @ORM\OneToMany(targetEntity="QuestionAnswer", mappedBy="place")
     */
    protected $qas;

    /**
     * @param mixed $qas
     */
    public function setQas($qas)
    {
        $this->qas = $qas;
    }

    /**
     * @return mixed
     */
    public function getQas()
    {
        return $this->qas;
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
    public function getTitle()
    {
        return $this->title;
    }

    public function __toString(){
        return $this->title;
    }

}
