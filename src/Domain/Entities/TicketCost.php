<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ticketcosts")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
#[ORM\Index(name: "begin_date", columns: ["begin_date"])]
#[ORM\Index(name: "end_date", columns: ["end_date"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "budgets_id", columns: ["budgets_id"])]
class TicketCost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'date', nullable: true)]
    private $begin_date;

    #[ORM\Column(type: 'date', nullable: true)]
    private $end_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"])]
    private $cost_time;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"])]
    private $cost_fixed;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"])]
    private $cost_material;

    #[ORM\ManyToOne(targetEntity: Budget::class)]
    #[ORM\JoinColumn(name: 'budgets_id', referencedColumnName: 'id', nullable: true)]
    private ?Budget $budgets;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getBeginDate(): ?\DateTime
    {
        return $this->begin_date;
    }

    public function setBeginDate(?\DateTime $begin_date): self
    {
        $this->begin_date = $begin_date;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTime $end_date): self
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getActiontime(): ?int
    {
        return $this->actiontime;
    }

    public function setActiontime(?int $actiontime): self
    {
        $this->actiontime = $actiontime;

        return $this;
    }

    public function getCostTime(): ?float
    {
        return $this->cost_time;
    }

    public function setCostTime(?float $cost_time): self
    {
        $this->cost_time = $cost_time;

        return $this;
    }

    public function getCostFixed(): ?float
    {
        return $this->cost_fixed;
    }

    public function setCostFixed(?float $cost_fixed): self
    {
        $this->cost_fixed = $cost_fixed;

        return $this;
    }

    public function getCostMaterial(): ?float
    {
        return $this->cost_material;
    }

    public function setCostMaterial(?float $cost_material): self
    {
        $this->cost_material = $cost_material;

        return $this;
    }


    /**
     * Get the value of ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set the value of ticket
     *
     * @return  self
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get the value of budgets
     */
    public function getBudgets()
    {
        return $this->budgets;
    }

    /**
     * Set the value of budgets
     *
     * @return  self
     */
    public function setBudgets($budgets)
    {
        $this->budgets = $budgets;

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
}
