<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_tickets")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["itemtype", "items_id", "tickets_id"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
class ItemTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    #[ORM\Column(name: 'tickets_id', type: "integer", options: ["default" => 0])]
    private $ticketsId;

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

    public function getTicketsId(): ?int
    {
        return $this->ticketsId;
    }

    public function setTicketsId(?int $ticketsId): self
    {
        $this->ticketsId = $ticketsId;

        return $this;
    }
}
