<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notifications_notificationtemplates')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['notifications_id', 'mode', 'notificationtemplates_id'])]
#[ORM\Index(name: 'notifications_id', columns: ['notifications_id'])]
#[ORM\Index(name: 'notificationtemplates_id', columns: ['notificationtemplates_id'])]
#[ORM\Index(name: 'mode', columns: ['mode'])]
class NotificationNotificationTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Notification::class, inversedBy: 'notificationNotificationTemplates')]
    #[ORM\JoinColumn(name: 'notifications_id', referencedColumnName: 'id', nullable: true)]
    private ?Notification $notification = null;

    #[ORM\Column(name: 'mode', type: 'string', length: 20, options: ['comment' => 'See Notification_NotificationTemplate::MODE_* constants'])]
    private $mode;

    #[ORM\ManyToOne(targetEntity: NotificationTemplate::class, inversedBy: 'notificationNotificationTemplates')]
    #[ORM\JoinColumn(name: 'notificationtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?NotificationTemplate $notificationTemplate = null;

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
    public function getNotificationTemplate()
    {
        return $this->notificationTemplate;
    }

    /**
     * Set the value of notificationtemplate
     *
     * @return  self
     */
    public function setNotificationTemplate($notificationtemplate)
    {
        $this->notificationTemplate = $notificationtemplate;

        return $this;
    }
}
