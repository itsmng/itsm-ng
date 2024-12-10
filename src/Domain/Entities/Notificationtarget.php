<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notificationtargets')]
#[ORM\Index(name: 'items', columns: ['type', 'items_id'])]
#[ORM\Index(name: 'notifications_id', columns: ['notifications_id'])]
class Notificationtarget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $type;

    #[ORM\ManyToOne(targetEntity: Notification::class)]
    #[ORM\JoinColumn(name: 'notifications_id', referencedColumnName: 'id', nullable: true)]
    private ?Notification $notification;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(?int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of notification
     */ 
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set the value of notification
     *
     * @return  self
     */ 
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }
}
