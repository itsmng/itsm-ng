<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_queuedchats')]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id", "notificationtemplates_id"])]
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

    #[ORM\ManyToOne(targetEntity: Notificationtemplate::class)]
    #[ORM\JoinColumn(name: 'notificationtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Notificationtemplate $notificationtemplate;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group;

    #[ORM\ManyToOne(targetEntity: ItilCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ItilCategory $itilcategory;

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


    /**
     * Get the value of entity
     */ 
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */ 
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of location
     */ 
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */ 
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of group
     */ 
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of group
     *
     * @return  self
     */ 
    public function setGroup($group)
    {
        $this->group = $group;

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

    /**
     * Get the value of itilcategory
     */ 
    public function getItilcategory()
    {
        return $this->itilcategory;
    }

    /**
     * Set the value of itilcategory
     *
     * @return  self
     */ 
    public function setItilcategory($itilcategory)
    {
        $this->itilcategory = $itilcategory;

        return $this;
    }
}
