<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_calendarsegments")]
#[ORM\Index(name: "calendars_id", columns: ["calendars_id"])]
#[ORM\Index(name: "day", columns: ["day"])]
class CalendarSegment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $calendars_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "boolean", options: ["default" => true, "comment" => "number of the day based on date(w)"])]
    private $day;

    #[ORM\Column(type: "time", nullable: true)]
    private $begin;

    #[ORM\Column(type: "time", nullable: true)]
    private $end;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function isRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getBegin(): ?string
    {
        return $this->begin;
    }

    public function setBegin(?string $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(?string $end): self
    {
        $this->end = $end;

        return $this;
    }
}
