<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="Vidal\MainBundle\Entity\UserDeviceRepository") @ORM\Table(name="user_device") */
class UserDevice
{
    const PROJECT_NEURO = 'neuro';
    const PROJECT_GYNECO = 'gyneco';

	/** @ORM\Column(type="integer") @ORM\Id @ORM\GeneratedValue */
	protected $id;

	/**
	 * @ORM\Column(length=500, nullable=true)
	 */
	protected $androidId;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $gcm;

    /**
     * @ORM\ManyToOne(targetEntity = "User", inversedBy = "devices")
     */
    protected $user;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    protected $project;

	/** @ORM\Column(type="boolean") */
	protected $ios = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAndroidId()
    {
        return $this->androidId;
    }

    /**
     * @param mixed $androidId
     */
    public function setAndroidId($androidId)
    {
        $this->androidId = $androidId;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getGcm()
    {
        return $this->gcm;
    }

    /**
     * @param mixed $gcm
     */
    public function setGcm($gcm)
    {
        $this->gcm = $gcm;
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
	public function getIos()
	{
		return $this->ios;
	}

	/**
	 * @param mixed $ios
	 */
	public function setIos($ios)
	{
		$this->ios = $ios;
	}
}