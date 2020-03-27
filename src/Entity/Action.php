<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActionRepository")
 * @ORM\Table(name="actions")
 */
class Action
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var String
     * @ORM\Column(type="string", length=50, nullable=false)
     *
     */
    protected $store;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $storeId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, length=255)
     */
    protected $storeName;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    protected $slotOpen;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    protected $onBreak;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->slotOpen = false;
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
     * @return String
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param String $store
     *
     * @return $this
     */
    public function setStore($store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;

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
     * @return bool
     */
    public function isSlotOpen()
    {
        return $this->slotOpen;
    }

    /**
     * @param bool $slotOpen
     *
     * @return $this
     */
    public function setSlotOpen($slotOpen)
    {
        $this->slotOpen = $slotOpen;

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
     * @param bool $onbreak
     *
     * @return $this
     */
    public function setOnBreak($onBreak)
    {
        $this->onBreak = $onBreak;

        return $this;
    }


    /**
     * Get the value of storeName
     *
     * @return  string
     */ 
    public function getStoreName()
    {
        return $this->storeName;
    }

    /**
     * Set the value of storeName
     *
     * @param  string  $storeName
     *
     * @return  self
     */ 
    public function setStoreName(string $storeName)
    {
        $this->storeName = $storeName;

        return $this;
    }
}