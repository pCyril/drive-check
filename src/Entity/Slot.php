<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SlotRepository")
 * @ORM\Table(name="slots")
 */
class Slot
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $open;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Store", inversedBy="slots",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $store;
    

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->open = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isOpen(): ?bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): self
    {
        $this->open = $open;

        return $this;
    }

    public function getStore(): ?Store
    {
        return $this->store;
    }

    public function setStore(?Store $store): self
    {
        $this->store = $store;

        return $this;
    }
}
