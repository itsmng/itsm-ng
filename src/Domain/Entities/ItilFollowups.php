<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

//Table: glpi_itilfollowups
//
//Select data Show structure Alter table New item
//Column	Type	Comment
//id	int(11) Auto Increment
//itemtype	varchar(100)
//items_id	int(11) [0]
//date	timestamp NULL
//users_id	int(11) [0]
//users_id_editor	int(11) [0]
//content	longtext NULL
//is_private	tinyint(1) [0]
//requesttypes_id	int(11) [0]
//date_mod	timestamp NULL
//date_creation	timestamp NULL
//timeline_position	tinyint(1) [0]
//sourceitems_id	int(11) [0]
//sourceof_items_id	int(11) [0]
//Indexes
//PRIMARY	id
//INDEX	itemtype
//INDEX	items_id
//INDEX	itemtype, items_id
//INDEX	date
//INDEX	date_mod
//INDEX	date_creation
//INDEX	users_id
//INDEX	users_id_editor
//INDEX	is_private
//INDEX	requesttypes_id
//INDEX	sourceitems_id
//INDEX	sourceof_items_id

#[ORM\Entity]
#[ORM\Table(name: 'glpi_itilfollowups')]
#[ORM\Index(columns: ['itemtype'])]
#[ORM\Index(columns: ['items_id'])]
#[ORM\Index(columns: ['itemtype', 'items_id'])]
#[ORM\Index(columns: ['date'])]
#[ORM\Index(columns: ['date_mod'])]
#[ORM\Index(columns: ['date_creation'])]
#[ORM\Index(columns: ['users_id'])]
#[ORM\Index(columns: ['users_id_editor'])]
#[ORM\Index(columns: ['is_private'])]
#[ORM\Index(columns: ['requesttypes_id'])]
#[ORM\Index(columns: ['sourceitems_id'])]
#[ORM\Index(columns: ['sourceof_items_id'])]
class ItilFollowups
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_editor;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_private;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $requesttypes_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $timeline_position;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $sourceitems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $sourceof_items_id;

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
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

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

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getUsersIdEditor(): ?int
    {
        return $this->users_id_editor;
    }

    public function setUsersIdEditor(int $users_id_editor): self
    {
        $this->users_id_editor = $users_id_editor;

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
        return $this->is_private;
    }

    public function setIsPrivate(bool $is_private): self
    {
        $this->is_private = $is_private;

        return $this;
    }

    public function getRequesttypesId(): ?int
    {
        return $this->requesttypes_id;
    }

    public function setRequesttypesId(int $requesttypes_id): self
    {
        $this->requesttypes_id = $requesttypes_id;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getTimelinePosition(): ?int
    {
        return $this->timeline_position;
    }

    public function setTimelinePosition(int $timeline_position): self
    {
        $this->timeline_position = $timeline_position;

        return $this;
    }

    public function getSourceitemsId(): ?int
    {
        return $this->sourceitems_id;
    }

    public function setSourceitemsId(int $source_items_id): self
    {
        $this->sourceitems_id = $source_items_id;

        return $this;
    }

    public function getSourceofItemsId(): ?int
    {
        return $this->sourceof_items_id;
    }

    public function setSourceofItemsId(int $sourceof_items_id): self
    {
        $this->sourceof_items_id = $sourceof_items_id;

        return $this;
    }
}
