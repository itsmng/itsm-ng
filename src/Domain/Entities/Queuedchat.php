<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_queuedchats')]
#[ORM\Index(name: "itemtype_items_id_notificationtemplates_id", columns: ["itemtype", "items_id", "notificationtemplates_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "sent_try", columns: ["sent_try"])]
#[ORM\Index(name: "create_time", columns: ["create_time"])]
#[ORM\Index(name: "send_time", columns: ["send_time"])]
#[ORM\Index(name: "sent_time", columns: ["sent_time"])]
#[ORM\Index(name: "mode", columns: ["mode"])]
class Queuedchat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $notificationtemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $itilcategories_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $sent_try;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $create_time;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $send_time;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $sent_time;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $entName;

    #[ORM\Column(type: 'text', length:  65535, nullable: true)]
    private $ticketTitle;

    #[ORM\Column(type: 'text', length:  65535, nullable: true)]
    private $completName;

    #[ORM\Column(type: 'text', length:  65535, nullable: true)]
    private $serverName;

    #[ORM\Column(type: 'string', length: 250, nullable: true)]
    private $hookurl;

    #[ORM\Column(type: 'string', length: 20, options: ['comment' => 'See Notification_NotificationTemplate::MODE_* constants'])]
    private $mode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
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

    public function getNotificationtemplatesId(): ?int
    {
        return $this->notificationtemplates_id;
    }

    public function setNotificationtemplatesId(?int $notificationtemplates_id): self
    {
        $this->notificationtemplates_id = $notificationtemplates_id;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }   

    public function setLocationsId(?int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(?int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
    }

    public function getItilcategoriesId(): ?int
    {
        return $this->itilcategories_id;
    }

    public function setItilcategoriesId(?int $itilcategories_id): self
    {
        $this->itilcategories_id = $itilcategories_id;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getSentTry(): ?int
    {
        return $this->sent_try;
    }

    public function setSentTry(?int $sent_try): self
    {
        $this->sent_try = $sent_try;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->create_time;
    }

    public function setCreateTime(?\DateTimeInterface $create_time): self
    {
        $this->create_time = $create_time;

        return $this;
    }

    public function getSendTime(): ?\DateTimeInterface
    {
        return $this->send_time;
    }

    public function setSendTime(?\DateTimeInterface $send_time): self
    {
        $this->send_time = $send_time;

        return $this;
    }

    public function getSentTime(): ?\DateTimeInterface
    {
        return $this->sent_time;
    }

    public function setSentTime(?\DateTimeInterface $sent_time): self
    {
        $this->sent_time = $sent_time;

        return $this;
    }

    public function getEntName(): ?string
    {
        return $this->entName;
    }

    public function setEntName(?string $entName): self
    {
        $this->entName = $entName;

        return $this;
    }

    public function getTicketTitle(): ?string
    {
        return $this->ticketTitle;
    }

    public function setTicketTitle(?string $ticketTitle): self
    {
        $this->ticketTitle = $ticketTitle;

        return $this;
    }

    public function getCompletName(): ?string
    {
        return $this->completName;
    }

    public function setCompletName(?string $completName): self
    {
        $this->completName = $completName;

        return $this;
    }

    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    public function setServerName(?string $serverName): self
    {
        $this->serverName = $serverName;

        return $this;
    }

    public function getHookurl(): ?string
    {
        return $this->hookurl;
    }

    public function setHookurl(?string $hookurl): self
    {
        $this->hookurl = $hookurl;

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

}