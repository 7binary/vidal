<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="PollAnswer")
 * @Filestore\Uploadable
 */
class PollAnswer extends BaseEntity
{
    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity = "Poll", inversedBy = "answers")
     */
    protected $poll;

    /**
     * @ORM\ManyToOne(targetEntity = "PollQuestion", inversedBy = "answers")
     */
    protected $question;

    /**
     * @param mixed $poll
     */
    public function setPoll($poll)
    {
        $this->poll = $poll;
    }

    /**
     * @return mixed
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * @param mixed $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return mixed
     */
    public function getQuestion()
    {
        return $this->question;
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


}
