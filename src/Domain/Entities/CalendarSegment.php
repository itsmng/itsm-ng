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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;


    #[ORM\ManyToOne(targetEntity: Calendar::class)]
    #[ORM\JoinColumn(name: 'calendars_id', referencedColumnName: 'id', nullable: true)]
    private ?Calendar $calendars = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => false])]
    private $isRecursive;

    #[ORM\Column(name: 'day', type: "boolean", options: ["default" => true, "comment" => "number of the day based on date(w)"])]
    private $day;

    #[ORM\Column(name: 'begin', type: "time", nullable: true)]
    private $begin;

    #[ORM\Column(name: 'end', type: "time", nullable: true)]
    private $end;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function isRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    /**
     * Get the value of calendars
     */
    public function getCalendars()
    {
        return $this->calendars;
    }

    /**
     * Set the value of calendars
     *
     * @return  self
     */
    public function setCalendars($calendars)
    {
        $this->calendars = $calendars;

        return $this;
    }
}
