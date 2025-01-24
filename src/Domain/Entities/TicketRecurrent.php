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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'entities_id', type: 'integer', options: ['default' => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 0])]
    private $isActive;

    #[ORM\Column(name: 'tickettemplates_id', type: 'integer', options: ['default' => 0])]
    private $tickettemplatesId;

    #[ORM\Column(name: 'begin_date', type: 'datetime', nullable: true)]
    private $beginDate;

    #[ORM\Column(name: 'periodicity', type: 'string', length: 255, nullable: true)]
    private $periodicity;

    #[ORM\Column(name: 'create_before', type: 'integer', options: ['default' => 0])]
    private $createBefore;

    #[ORM\Column(name: 'next_creation_date', type: 'datetime', nullable: true)]
    private $nextCreationDate;

    #[ORM\Column(name: 'calendars_id', type: 'integer', options: ['default' => 0])]
    private $calendarsId;

    #[ORM\Column(name: 'end_date', type: 'datetime', nullable: true)]
    private $endDate;

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
        return $this->entitiesId;
    }

    public function setEntitiesId(?int $entitiesId): self
    {
        $this->entitiesId = $entitiesId;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTickettemplatesId(): ?int
    {
        return $this->tickettemplatesId;
    }

    public function setTickettemplatesId(?int $tickettemplatesId): self
    {
        $this->tickettemplatesId = $tickettemplatesId;

        return $this;
    }

    public function getBeginDate(): ?\DateTime
    {
        return $this->beginDate;
    }

    public function setBeginDate(?\DateTime $beginDate): self
    {
        $this->beginDate = $beginDate;

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
        return $this->createBefore;
    }

    public function setCreateBefore(?int $createBefore): self
    {
        $this->createBefore = $createBefore;

        return $this;
    }

    public function getNextCreationDate(): ?\DateTime
    {
        return $this->nextCreationDate;
    }

    public function setNextCreationDate(?\DateTime $nextCreationDate): self
    {
        $this->nextCreationDate = $nextCreationDate;

        return $this;
    }

    public function getCalendarsId(): ?int
    {
        return $this->calendarsId;
    }

    public function setCalendarsId(?int $calendarsId): self
    {
        $this->calendarsId = $calendarsId;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

}
