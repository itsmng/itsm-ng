<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ticketrecurrents")]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "tickettemplates_id", columns: ["tickettemplates_id"])]
#[ORM\Index(name: "next_creation_date", columns: ["next_creation_date"])]
class TicketRecurrent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_active;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickettemplates_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin_date;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $periodicity;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $create_before;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $next_creation_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $calendars_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $end_date;

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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getTickettemplatesId(): ?int
    {
        return $this->tickettemplates_id;
    }

    public function setTickettemplatesId(?int $tickettemplates_id): self
    {
        $this->tickettemplates_id = $tickettemplates_id;

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

    public function getPeriodicity(): ?string
    {
        return $this->periodicity;
    }

    public function setPeriodicity(?string $periodicity): self
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    public function getCreateBefore(): ?int
    {
        return $this->create_before;
    }

    public function setCreateBefore(?int $create_before): self
    {
        $this->create_before = $create_before;

        return $this;
    }

    public function getNextCreationDate(): ?\DateTime
    {
        return $this->next_creation_date;
    }

    public function setNextCreationDate(?\DateTime $next_creation_date): self
    {
        $this->next_creation_date = $next_creation_date;

        return $this;
    }

    public function getCalendarsId(): ?int
    {
        return $this->calendars_id;
    }

    public function setCalendarsId(?int $calendars_id): self
    {
        $this->calendars_id = $calendars_id;

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

}
