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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket = null;

    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(name: 'date_begin', type: 'datetime', nullable: true)]
    private $dateBegin;

    #[ORM\Column(name: 'date_answered', type: 'datetime', nullable: true)]
    private $dateAnswered;

    #[ORM\Column(name: 'satisfaction', type: 'integer', nullable: true)]
    private $satisfaction;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->dateBegin;
    }

    public function setDateBegin(?\DateTime $dateBegin): self
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }

    public function getDateAnswered(): ?\DateTime
    {
        return $this->dateAnswered;
    }

    public function setDateAnswered(?\DateTime $dateAnswered): self
    {
        $this->dateAnswered = $dateAnswered;

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


    /**
     * Get the value of ticket
     */
    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    /**
     * Set the value of ticket
     *
     * @return  self
     */
    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }
}
