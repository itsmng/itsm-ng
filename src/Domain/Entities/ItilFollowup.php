<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use RequestType;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_itilfollowups')]
#[ORM\Index(name: "itemtype", columns: ['itemtype'])]
#[ORM\Index(name: "item_id", columns: ['items_id'])]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "date", columns: ['date'])]
#[ORM\Index(name: "date_mod", columns: ['date_mod'])]
#[ORM\Index(name: "date_creation", columns: ['date_creation'])]
#[ORM\Index(name: "users_id", columns: ['users_id'])]
#[ORM\Index(name: "users_id_editor", columns: ['users_id_editor'])]
#[ORM\Index(name: "is_private", columns: ['is_private'])]
#[ORM\Index(name: "requesttypes_id", columns: ['requesttypes_id'])]
#[ORM\Index(name: "sourceitems_id", columns: ['sourceitems_id'])]
#[ORM\Index(name: "sourceof_items_id", columns: ['sourceof_items_id'])]
class ItilFollowup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_editor', referencedColumnName: 'id', nullable: true)]
    private ?User $userEditor = null;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(name: 'is_private', type: 'boolean', options: ['default' => 0])]
    private $isPrivate;

    #[ORM\ManyToOne(targetEntity: RequestType::class)]
    #[ORM\JoinColumn(name: 'requesttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?RequestType $requesttype = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'timeline_position', type: 'boolean', options: ['default' => 0])]
    private $timelinePosition;

    #[ORM\Column(name: 'sourceitems_id', type: 'integer', options: ['default' => 0])]
    private $sourceitemsId;

    #[ORM\Column(name: 'sourceof_items_id', type: 'integer', options: ['default' => 0])]
    private $sourceofItemsId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getTimelinePosition(): ?int
    {
        return $this->timelinePosition;
    }

    public function setTimelinePosition(int $timelinePosition): self
    {
        $this->timelinePosition = $timelinePosition;

        return $this;
    }

    public function getSourceitemsId(): ?int
    {
        return $this->sourceitemsId;
    }

    public function setSourceitemsId(int $sourceItemsId): self
    {
        $this->sourceitemsId = $sourceItemsId;

        return $this;
    }

    public function getSourceofItemsId(): ?int
    {
        return $this->sourceofItemsId;
    }

    public function setSourceofItemsId(int $sourceofItemsId): self
    {
        $this->sourceofItemsId = $sourceofItemsId;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of userEditor
     */
    public function getUserEditor()
    {
        return $this->userEditor;
    }

    /**
     * Set the value of userEditor
     *
     * @return  self
     */
    public function setUserEditor($userEditor)
    {
        $this->userEditor = $userEditor;

        return $this;
    }

    /**
     * Get the value of requesttype
     */
    public function getRequesttype()
    {
        return $this->requesttype;
    }

    /**
     * Set the value of requesttype
     *
     * @return  self
     */
    public function setRequesttype($requesttype)
    {
        $this->requesttype = $requesttype;

        return $this;
    }


}
