<?php
namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass = "PollQuestionRepository")
 * @ORM\Table(name="PollQuestion")
 */
class PollQuestion extends BaseEntity
{
    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity = "Poll", inversedBy = "questions")
     */
    protected $poll;

    /**
     * @ORM\OneToMany(targetEntity = "PollOption", mappedBy = "question")
     */
    protected $options;

    /**
     * @ORM\OneToMany(targetEntity = "PollAnswer", mappedBy = "question")
     */
    protected $answers;

    public function __construct(){
        $this->answers = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function addAnswer($answer){
        $this->answers[] = $answer;
    }

    public function removeAnswer($answer){
        $this->answers->removeElement($answer);
    }

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
     * @param mixed $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * @return mixed
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param mixed $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    /**
     * @return mixed
     */
    public function getQuestions()
    {
        return $this->questions;
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

    public function addOption($option){
        $this->options[] = $option;
    }

    public function removeOption($option){
        $this->options->removeElement($option);
    }

    /**
    * @param mixed $options
    */
    public function setOptions($options)
    {
        $this->options = $options;
    }
    /**
    * @return mixed
    */
    public function getOptions()
    {
        return $this->options;
    }
}
