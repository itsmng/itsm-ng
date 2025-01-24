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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 0])]
    private $type;

    #[ORM\ManyToOne(targetEntity: Notification::class)]
    #[ORM\JoinColumn(name: 'notifications_id', referencedColumnName: 'id', nullable: true)]
    private ?Notification $notification = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(?int $itemsId): self
    {
        $this->itemsId = $itemsId;

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
