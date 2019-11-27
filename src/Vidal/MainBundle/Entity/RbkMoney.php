<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Entity(repositoryClass="RbkMoneyRepository") @ORM\Table(name="rbkmoney") */
class RbkMoney extends BaseEntity
{
    const DEFAULT_ESHOP_ID = '2039696';

    /**
     * @var string
     * @ORM\Column(length=500)
     * @Assert\NotBlank(message="Укажите название товара")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Укажите цену товара")
     */
    protected $price;

    /**
     * @ORM\Column(length=50)
     * @Assert\NotBlank(message="Укажите уникальный ID заказа")
     */
    protected $orderId;

    /**
     * @ORM\Column(type = "integer", nullable=true)
     */
    protected $user_id;

    /**
     * @ORM\Column(length=100, nullable=true)
     */
    protected $user_email;

    /**
     * @ORM\Column(length=100, nullable=true)
     */
    protected $eshopId;

    /** @ORM\Column(type="boolean") */
    protected $sent = false;

    /** @ORM\Column(type="boolean") */
    protected $paid = false;

    /** @ORM\Column(type="boolean") */
    protected $failed = false;

    /** @ORM\Column(type="text", nullable=true) */
    protected $status;

    public function __construct()
    {
        if (empty($this->orderId)) {
            $this->orderId = uniqid();
        }
        if (empty($this->eshopId)) {
            $this->eshopId = self::DEFAULT_ESHOP_ID;
        }
    }

    public function __toString()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getUserEmail()
    {
        return $this->user_email;
    }

    /**
     * @param mixed $user_email
     */
    public function setUserEmail($user_email)
    {
        $this->user_email = $user_email;
    }

    /**
     * @return mixed
     */
    public function getEshopId()
    {
        return $this->eshopId;
    }

    /**
     * @param mixed $eshopId
     */
    public function setEshopId($eshopId)
    {
        $this->eshopId = $eshopId;
    }

    /**
     * @return mixed
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * @param mixed $sent
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @param mixed $paid
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
    }

    /**
     * @return mixed
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * @param mixed $failed
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;
    }
}