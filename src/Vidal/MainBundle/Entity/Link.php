<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity() @ORM\Table(name="link") */
class Link extends BaseEntity
{
	/** @ORM\Column(type="text", nullable=true) */
	protected $text;

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}