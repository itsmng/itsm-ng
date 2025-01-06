<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notifications_notificationtemplates')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['notifications_id', 'mode', 'notificationtemplates_id'])]
#[ORM\Index(name: 'notifications_id', columns: ['notifications_id'])]
#[ORM\Index(name: 'notificationtemplates_id', columns: ['notificationtemplates_id'])]
#[ORM\Index(name: 'mode', columns: ['mode'])]
class NotificationNotificationtemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Notification::class, inversedBy: 'notificationNotificationtemplates')]
    #[ORM\JoinColumn(name: 'notifications_id', referencedColumnName: 'id', nullable: true)]
    private ?Notification $notification;

    #[ORM\Column(type: 'string', length: 20, options: ['comment' => 'See Notification_NotificationTemplate::MODE_* constants'])]
    private $mode;

    #[ORM\ManyToOne(targetEntity: Notificationtemplate::class, inversedBy: 'notificationNotificationtemplates')]
    #[ORM\JoinColumn(name: 'notificationtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Notificationtemplate $notificationtemplate;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * Get the value of notificationtemplate
     */
    public function getNotificationtemplate()
    {
        return $this->notificationtemplate;
    }

    /**
     * Set the value of notificationtemplate
     *
     * @return  self
     */
    public function setNotificationtemplate($notificationtemplate)
    {
        $this->notificationtemplate = $notificationtemplate;

        return $this;
    }
}
