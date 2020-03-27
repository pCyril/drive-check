<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductAlertRepository")
 * @ORM\Table(name="product_alerts")
 */
class ProductAlert
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastCheck;

    /**
     * @var Store
     *
     * @ORM\ManyToOne(targetEntity="Store")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=false)
     */
    protected $store;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    protected $stockOut;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $productId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $productName;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    protected $onBreak;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->stockOut = true;
        $this->onBreak = false;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param Store $store
     *
     * @return $this
     */
    public function setStore($store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStockOut()
    {
        return $this->stockOut;
    }

    /**
     * @param bool $stockOut
     *
     * @return $this
     */
    public function setStockOut($stockOut)
    {
        $this->stockOut = $stockOut;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param \DateTime $lastCheck
     *
     * @return $this
     */
    public function setLastCheck($lastCheck)
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     *
     * @return $this
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     *
     * @return $this
     */
    public function setProductId(int $productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOnBreak()
    {
        return $this->onBreak;
    }

    /**
     * @param bool $onBreak
     *
     * @return $this
     */
    public function setOnBreak($onBreak)
    {
        $this->onBreak = $onBreak;

        return $this;
    }
}