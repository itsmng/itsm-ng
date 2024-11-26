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

    #[ORM\ManyToOne(targetEntity: Calendar::class, inversedBy: 'calendarholidays')]
    #[ORM\JoinColumn(name: 'calendars_id', referencedColumnName: 'id', nullable: false)]
    private ?Calendar $calendar;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $holidays_id;

    #[ORM\ManyToOne(targetEntity: Holiday::class, inversedBy: 'calendarholidays')]
    #[ORM\JoinColumn(name: 'holidays_id', referencedColumnName: 'id', nullable: false)]
    private ?Holiday $holiday;

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

    /**
     * Get the value of calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Set the value of calendar
     *
     * @return  self
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Get the value of holiday
     */
    public function getHoliday()
    {
        return $this->holiday;
    }

    /**
     * Set the value of holiday
     *
     * @return  self
     */
    public function setHoliday($holiday)
    {
        $this->holiday = $holiday;

        return $this;
    }
}
