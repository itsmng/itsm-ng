<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notifications_notificationtemplates')]
#[ORM\UniqueConstraint(name: 'notifications_id_mode_notificationtemplates_id', columns: ['notifications_id', 'mode', 'notificationtemplates_id'])]
#[ORM\Index(name: 'notifications_id', columns: ['notifications_id'])]
#[ORM\Index(name: 'notificationtemplates_id', columns: ['notificationtemplates_id'])]
#[ORM\Index(name: 'mode', columns: ['mode'])]
class NotificationNotificationtemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $notifications_id;
    
    #[ORM\Column(type: 'string', length: 20, options: ['comment' => 'See Notification_NotificationTemplate::MODE_* constants'])]
    private $mode;
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $notificationtemplates_id;

    public function getId(): ?int
    {
        return $this->id;
    }    

    public function getNotificationsId(): ?int
    {
        return $this->notifications_id;
    }

    public function setNotificationsId(?int $notifications_id): self
    {
        $this->notifications_id = $notifications_id;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getNotificationtemplatesId(): ?int
    {
        return $this->notificationtemplates_id;
    }

    public function setNotificationtemplatesId(?int $notificationtemplates_id): self
    {
        $this->notificationtemplates_id = $notificationtemplates_id;

        return $this;
    }

}