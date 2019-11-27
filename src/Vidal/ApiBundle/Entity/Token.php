<?php

namespace Vidal\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @inheritdoc
 * @ORM\Entity(repositoryClass="TokenRepository")
 * @ORM\Table(name="api_token")
 */
class Token extends BaseEntity
{
    /**
     * @ORM\Column(type="string", name="user_name", nullable=true)
     */
    protected $userName;

    /**
     * @ORM\Column(type="string", name="user_password", nullable=true)
     */
    protected $userPassword;

    /**
    * @ORM\Column(type="integer", name="max_request_per_day", nullable=true)
    */
    protected $maxRequestPerDay;

    /**
    * @ORM\Column(type="integer", name="current_request_per_day", nullable=true)
    */
    protected $currentRequestPerDay;

    /**
     * @ORM\Column(type="string", name="comment", nullable=true)
     */
    protected $comment;

	/**
	 * @ORM\Column(type = "datetime", name="last_request_date", nullable=true)
	 */
	protected $lastRequestDate;

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->userName;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param mixed $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return mixed
     */
    public function getUserPassword()
    {
        return $this->userPassword;
    }

    /**
     * @param mixed $userName
     */
    public function setUserPassword($userPassword)
    {
        $this->userPassword = $userPassword;
    }

    /**
     * @return mixed
     */
    public function getMaxRequestPerDay()
    {
        return $this->maxRequestPerDay;
    }

    /**
     * @param mixed $maxRequestPerDay
     */
    public function setMaxRequestPerDay($maxRequestPerDay)
    {
        $this->maxRequestPerDay = $maxRequestPerDay;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getLastRequestDate()
    {
        return $this->lastRequestDate;
    }

    /**
     * @param mixed $lastRequestDate
     */
    public function setLastRequestDate($lastRequestDate)
    {
        $this->lastRequestDate = $lastRequestDate;
    }

    /**
     * @return mixed
     */
    public function getCurrentRequestPerDay()
    {
        return $this->currentRequestPerDay;
    }

    /**
     * @param mixed $currentRequestPerDay
     */
    public function setCurrentRequestPerDay($currentRequestPerDay)
    {
        $this->currentRequestPerDay = $currentRequestPerDay;
    }

    public function increaseCurrentRequestPerDay()
    {
        $lastRequestDate = $this->getLastRequestDate();
        $curDate = new \DateTime();
        $curDate = $curDate->setTime(0, 0, 1);

        if($lastRequestDate) {
            if($lastRequestDate < $curDate) {
                $this->currentRequestPerDay = 0;
            }
        }

        $this->currentRequestPerDay = $this->currentRequestPerDay + 1;

        return $this->currentRequestPerDay;
    }
}