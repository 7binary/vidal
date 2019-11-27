<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/** @ORM\Entity(repositoryClass="PushRepository") @ORM\Table(name="push")*/
class Push extends BaseEntity
{
	/**
	 * @ORM\Column(type="text")
	 */
	protected $request;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $response;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $gcm;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="Publication", inversedBy="pushes")
     */
    protected $publication;

	public function __construct()
	{
		$now           = new \DateTime('now');
		$this->created = $now;
		$this->updated = $now;
	}

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
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
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * @param mixed $publication
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;
    }
}