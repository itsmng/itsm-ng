<?php

namespace Itsmng\Domain\Entities;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_logs')]
#[ORM\Index(name: "date_mod", columns: ['date_mod'])]
#[ORM\Index(name: "itemtype_link", columns: ['itemtype_link'])]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "id_search_option", columns: ['id_search_option'])]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100, options: ['default' => ''])]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype_link', type: 'string', length: 100, options: ['default' => ''])]
    private $itemtypeLink;

    #[ORM\Column(name: 'linked_action', type: 'integer', options: ['default' => 0, 'comment' => 'see define.php HISTORY_* constant'])]
    private $linkedAction;

    #[ORM\Column(name: 'user_name', type: 'string', length: 255, nullable: true)]
    private $userName;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'id_search_option', type: 'integer', options: ['default' => 0, 'comment' => 'see search.constant.php for value'])]
    private $idSearchOption;

    #[ORM\Column(name: 'old_value', type: 'string', length: 255, nullable: true)]
    private $oldValue;

    #[ORM\Column(name: 'new_value', type: 'string', length: 255, nullable: true)]
    private $newValue;

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

    public function getItemtypeLink(): ?string
    {
        return $this->itemtypeLink;
    }

    public function setItemtypeLink(string $itemtypeLink): self
    {
        $this->itemtypeLink = $itemtypeLink;

        return $this;
    }

    public function getLinkedAction(): ?int
    {
        return $this->linkedAction;
    }

    public function setLinkedAction(int $linkedAction): self
    {
        $this->linkedAction = $linkedAction;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    #[ORM\PreUpdate]
    #[ORM\PreFlush]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTimeImmutable();

        return $this;
    }

    public function getIdSearchOption(): ?int
    {
        return $this->idSearchOption;
    }

    public function setIdSearchOption(int $idSearchOption): self
    {
        $this->idSearchOption = $idSearchOption;

        return $this;
    }

    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    public function setOldValue(?string $oldValue): self
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    public function setNewValue(?string $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }
}
