<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_logs')]
#[ORM\Index(columns: ['date_mod'])]
#[ORM\Index(columns: ['itemtype_link'])]
#[ORM\Index(columns: ['itemtype', 'items_id'])]
#[ORM\Index(columns: ['id_search_option'])]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100, options: ['default' => ''])]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100, options: ['default' => ''])]
    private $itemtype_link;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'see define.php HISTORY_* constant'])]
    private $linked_action;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $user_name;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'see search.constant.php for value'])]
    private $id_search_option;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $old_value;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $new_value;

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

    public function getItemtypeLink(): ?string
    {
        return $this->itemtype_link;
    }

    public function setItemtypeLink(string $itemtype_link): self
    {
        $this->itemtype_link = $itemtype_link;

        return $this;
    }

    public function getLinkedAction(): ?int
    {
        return $this->linked_action;
    }

    public function setLinkedAction(int $linked_action): self
    {
        $this->linked_action = $linked_action;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function setUserName(?string $user_name): self
    {
        $this->user_name = $user_name;

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

    public function getIdSearchOption(): ?int
    {
        return $this->id_search_option;
    }

    public function setIdSearchOption(int $id_search_option): self
    {
        $this->id_search_option = $id_search_option;

        return $this;
    }

    public function getOldValue(): ?string
    {
        return $this->old_value;
    }

    public function setOldValue(?string $old_value): self
    {
        $this->old_value = $old_value;

        return $this;
    }

    public function getNewValue(): ?string
    {
        return $this->new_value;
    }

    public function setNewValue(?string $new_value): self
    {
        $this->new_value = $new_value;

        return $this;
    }
}
