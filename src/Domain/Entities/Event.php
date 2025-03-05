<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_events")]
#[Orm\Index(name: "name", columns: ["date"])]
#[Orm\Index(name: "level", columns: ["level"])]
#[Orm\Index(name: "item", columns: ["type", "items_id"])]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'items_id', type: "integer", nullable: false, options: ['default' => 0, 'unsigned' => true])]
    private $itemsId = 0;

    #[ORM\Column(name: 'type', type: "string", length: 255, nullable: true)]
    private $type;

    #[ORM\Column(name: 'date', type: "datetime", nullable: true)]
    private $date;

    #[ORM\Column(name: 'service', type: "string", length: 255, nullable: true)]
    private $service;

    #[ORM\Column(name: 'level', type: "integer", options: ['default' => 0])]
    private $level;

    #[ORM\Column(name: 'message', type: "text", length: 65535, nullable: true)]
    private $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId ?? 0;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId ?? 0;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
