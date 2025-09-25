<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_queuednotifications')]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id", "notificationtemplates_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "sent_try", columns: ["sent_try"])]
#[ORM\Index(name: "create_time", columns: ["create_time"])]
#[ORM\Index(name: "send_time", columns: ["send_time"])]
#[ORM\Index(name: "sent_time", columns: ["sent_time"])]
#[ORM\Index(name: "mode", columns: ["mode"])]
class QueuedNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $items_id = 0;

    #[ORM\ManyToOne(targetEntity: NotificationTemplate::class)]
    #[ORM\JoinColumn(name: 'notificationtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?NotificationTemplate $notificationTemplate = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'sent_try', type: 'integer', options: ['default' => 0])]
    private $sentTry = 0;

    #[ORM\Column(name: 'create_time', type: 'datetime', nullable: true)]
    private $createTime;

    #[ORM\Column(name: 'send_time', type: 'datetime', nullable: true)]
    private $sendTime;

    #[ORM\Column(name: 'sent_time', type: 'datetime', nullable: true)]
    private $sentTime;

    #[ORM\Column(name: 'name', type: 'text', length: 65535, nullable: true)]
    private $name;

    #[ORM\Column(name: 'sender', type: 'text', length: 65535, nullable: true)]
    private $sender;

    #[ORM\Column(name: 'sendername', type: 'text', length: 65535, nullable: true)]
    private $sendername;

    #[ORM\Column(name: 'recipient', type: 'text', length: 65535, nullable: true)]
    private $recipient;

    #[ORM\Column(name: 'recipientname', type: 'text', length: 65535, nullable: true)]
    private $recipientname;

    #[ORM\Column(name: 'replyto', type: 'text', length: 65535, nullable: true)]
    private $replyto;

    #[ORM\Column(name: 'replytoname', type: 'text', length: 65535, nullable: true)]
    private $replytoname;

    #[ORM\Column(name: 'headers', type: 'text', length: 65535, nullable: true)]
    private $headers;

    #[ORM\Column(name: 'body_html', type: 'text', nullable: true)]
    private $bodyHtml;

    #[ORM\Column(name: 'body_text', type: 'text', nullable: true)]
    private $bodyText;

    #[ORM\Column(name: 'messageid', type: 'text', length: 65535, nullable: true)]
    private $messageid;

    #[ORM\Column(name: 'documents', type: 'text', length: 65535, nullable: true)]
    private $documents;

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
        return $this->items_id;
    }

    public function setItemsId(?int $items_id): self
    {
        $this->items_id = $items_id;

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

    public function setCreateTime(\DateTimeInterface|string|null $createTime): self
    {
        if (is_string($createTime)) {
            $createTime = new \DateTime($createTime);
        }
        $this->createTime = $createTime;

        return $this;
    }

    public function getSendTime(): ?\DateTimeInterface
    {
        return $this->sendTime;
    }

    public function setSendTime(\DateTimeInterface|string|null $sendTime): self
    {
        if (is_string($sendTime)) {
            $sendTime = new \DateTime($sendTime);
        }
        $this->sendTime = $sendTime;

        return $this;
    }

    public function getSentTime(): ?\DateTimeInterface
    {
        return $this->sentTime;
    }

    public function setSentTime(\DateTimeInterface|string|null $sentTime): self
    {
        if (is_string($sentTime)) {
            $sentTime = new \DateTime($sentTime);
        }
        $this->sentTime = $sentTime;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(?string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getSendername(): ?string
    {
        return $this->sendername;
    }

    public function setSendername(?string $sendername): self
    {
        $this->sendername = $sendername;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function setRecipient(?string $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getRecipientname(): ?string
    {
        return $this->recipientname;
    }

    public function setRecipientname(?string $recipientname): self
    {
        $this->recipientname = $recipientname;

        return $this;
    }

    public function getReplyto(): ?string
    {
        return $this->replyto;
    }

    public function setReplyto(?string $replyto): self
    {
        $this->replyto = $replyto;

        return $this;
    }

    public function getReplytoname(): ?string
    {
        return $this->replytoname;
    }

    public function setReplytoname(?string $replytoname): self
    {
        $this->replytoname = $replytoname;

        return $this;
    }

    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    public function setHeaders(?string $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getBodyHtml(): ?string
    {
        return $this->bodyHtml;
    }

    public function setBodyHtml(?string $bodyHtml): self
    {
        $this->bodyHtml = $bodyHtml;

        return $this;
    }

    public function getBodyText(): ?string
    {
        return $this->bodyText;
    }

    public function setBodyText(?string $bodyText): self
    {
        $this->bodyText = $bodyText;

        return $this;
    }

    public function getMessageid(): ?string
    {
        return $this->messageid;
    }

    public function setMessageid(?string $messageid): self
    {
        $this->messageid = $messageid;

        return $this;
    }

    public function getDocuments(): ?string
    {
        return $this->documents;
    }

    public function setDocuments(?string $documents): self
    {
        $this->documents = $documents;

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
    public function setNotificationTemplate($notificationTemplate)
    {
        $this->notificationTemplate = $notificationTemplate;

        return $this;
    }
}
