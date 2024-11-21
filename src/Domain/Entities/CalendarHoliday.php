<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_calendars_holidays')]
#[ORM\UniqueConstraint(name: 'calendars_id_holidays_id', columns: ['calendars_id', 'holidays_id'])]
#[ORM\Index(name: 'holidays_id', columns: ['holidays_id'])]

class CalendarHoliday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $calendars_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $holidays_id;

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

    public function getHolidaysId(): ?int
    {
        return $this->holidays_id;
    }

    public function setHolidaysId(int $holidays_id): self
    {
        $this->holidays_id = $holidays_id;

        return $this;
    }
}