<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slas')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "calendars_id", columns: ["calendars_id"])]
#[ORM\Index(name: "slms_id", columns: ["slms_id"])]
class Sla
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $type;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer')]
    private $number_time;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $calendars_id;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $definition_time;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $end_of_working_day;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $slms_id;

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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getNumberTime(): ?int
    {
        return $this->number_time;
    }

    public function setNumberTime(int $number_time): self
    {
        $this->number_time = $number_time;

        return $this;
    }

    public function getCalendarsId(): ?int
    {
        return $this->calendars_id;
    }

    public function setCalendarsId(int $calendars_id): self
    {
        $this->calendars_id = $calendars_id;

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

    public function getDefinitionTime(): ?string
    {
        return $this->definition_time;
    }

    public function setDefinitionTime(?string $definition_time): self
    {
        $this->definition_time = $definition_time;

        return $this;
    }

    public function getEndOfWorkingDay(): ?bool
    {
        return $this->end_of_working_day;
    }

    public function setEndOfWorkingDay(bool $end_of_working_day): self
    {
        $this->end_of_working_day = $end_of_working_day;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }   

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;      
        return $this;
    }
    
    public function getslmsId(): ?\DateTimeInterface
    {
        return $this->slms_id;
    }

    public function setslmsId(?\DateTimeInterface $slms_id): self
    {
        $this->slms_id = $slms_id;
        return $this;
    }   

}

