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
class QueuedChat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\ManyToOne(targetEntity: NotificationTemplate::class)]
    #[ORM\JoinColumn(name: 'notificationtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?NotificationTemplate $notificationTemplate = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: ITILCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ITILCategory $itilcategory = null;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'sent_try', type: 'integer', options: ['default' => 0])]
    private $sentTry;

    #[ORM\Column(name: 'create_time', type: 'datetime', nullable: true)]
    private $createTime;

    #[ORM\Column(name: 'send_time', type: 'datetime', nullable: true)]
    private $sendTime;

    #[ORM\Column(name: 'sent_time', type: 'datetime', nullable: true)]
    private $sentTime;

    #[ORM\Column(name: 'ent_name', type: 'text', length: 65535, nullable: true)]
    private $entName;

    #[ORM\Column(name: 'ticket_title', type: 'text', length:  65535, nullable: true)]
    private $ticketTitle;

    #[ORM\Column(name: 'complet_name', type: 'text', length:  65535, nullable: true)]
    private $completName;

    #[ORM\Column(name: 'server_name', type: 'text', length:  65535, nullable: true)]
    private $serverName;

    #[ORM\Column(name: 'hookurl', type: 'string', length: 250, nullable: true)]
    private $hookurl;

    #[ORM\Column(name: 'mode', type: 'string', length: 20, options: ['comment' => 'See Notification_NotificationTemplate::MODE_* constants'])]
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
        return $this->itemsId;
    }

    public function setItemsId(?int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getSentTry(): ?int
    {
        return $this->sentTry;
    }

    public function setSentTry(?int $sentTry): self
    {
        $this->sentTry = $sentTry;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setCreateTime(?\DateTimeInterface $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    public function getSendTime(): ?\DateTimeInterface
    {
        return $this->sendTime;
    }

    public function setSendTime(?\DateTimeInterface $sendTime): self
    {
        $this->sendTime = $sendTime;

        return $this;
    }

    public function getSentTime(): ?\DateTimeInterface
    {
        return $this->sentTime;
    }

    public function setSentTime(?\DateTimeInterface $sentTime): self
    {
        $this->sentTime = $sentTime;

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
     * Get the value of notificationTemplate
     */
    public function getNotificationTemplate()
    {
        return $this->notificationTemplate;
    }

    /**
     * Set the value of notificationTemplate
     *
     * @return  self
     */
    public function setNotificationTemplate($notificationtemplate)
    {
        $this->notificationTemplate = $notificationtemplate;

        return $this;
    }

    /**
     * Get the value of itilcategory
     */
    public function getITILcategory()
    {
        return $this->itilcategory;
    }

    /**
     * Set the value of itilcategory
     *
     * @return  self
     */
    public function setITILcategory($itilcategory)
    {
        $this->itilcategory = $itilcategory;

        return $this;
    }
}
