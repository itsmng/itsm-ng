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
class Queuednotification
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
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $sender;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $sendername;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $recipient;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $recipientname;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $replyto;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $replytoname;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $headers;

    #[ORM\Column(type: 'text', nullable: true)]
    private $body_html;

    #[ORM\Column(type: 'text', nullable: true)]
    private $body_text;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $messageid;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $documents;

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
        return $this->body_html;
    }

    public function setBodyHtml(?string $body_html): self
    {
        $this->body_html = $body_html;

        return $this;
    }

    public function getBodyText(): ?string
    {
        return $this->body_text;
    }

    public function setBodyText(?string $body_text): self
    {
        $this->body_text = $body_text;

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
