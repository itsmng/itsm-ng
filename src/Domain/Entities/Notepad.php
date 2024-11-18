<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notepads')]
#[ORM\Index(name: 'itemtype_items_id', columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date', columns: ['date'])]
#[ORM\Index(name: 'users_id_lastupdater', columns: ['users_id_lastupdater'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
class Notepas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $itemtype;
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;
    
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_lastupdater;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(?int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getUsersIdLastupdater(): ?int
    {
        return $this->users_id_lastupdater;
    }

    public function setUsersIdLastupdater(?int $users_id_lastupdater): self
    {
        $this->users_id_lastupdater = $users_id_lastupdater;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

}