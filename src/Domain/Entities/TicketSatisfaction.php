<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ticketsatisfactions")]
#[ORM\UniqueConstraint(name: "tickets_id", columns: ["tickets_id"])]
class TicketSatisfaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickets_id;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_begin;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_answered;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $satisfaction;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicketsId(): ?int
    {
        return $this->tickets_id;
    }

    public function setTicketsId(?int $tickets_id): self
    {
        $this->tickets_id = $tickets_id;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateBegin(): ?\DateTime
    {
        return $this->date_begin;
    }

    public function setDateBegin(?\DateTime $date_begin): self
    {
        $this->date_begin = $date_begin;

        return $this;
    }

    public function getDateAnswered(): ?\DateTime
    {
        return $this->date_answered;
    }

    public function setDateAnswered(?\DateTime $date_answered): self
    {
        $this->date_answered = $date_answered;

        return $this;
    }

    public function getSatisfaction(): ?int
    {
        return $this->satisfaction;
    }

    public function setSatisfaction(?int $satisfaction): self
    {
        $this->satisfaction = $satisfaction;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

}
