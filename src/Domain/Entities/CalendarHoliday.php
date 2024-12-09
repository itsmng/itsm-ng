<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_calendars_holidays')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['calendars_id', 'holidays_id'])]
#[ORM\Index(name: 'holidays_id', columns: ['holidays_id'])]

class CalendarHoliday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Calendar::class, inversedBy: 'calendarHolidays')]
    #[ORM\JoinColumn(name: 'calendars_id', referencedColumnName: 'id', nullable: true)]
    private ?Calendar $calendar;

    #[ORM\ManyToOne(targetEntity: Holiday::class, inversedBy: 'calendarHolidays')]
    #[ORM\JoinColumn(name: 'holidays_id', referencedColumnName: 'id', nullable: true)]
    private ?Holiday $holiday;

    public function getId(): ?int
    {
        return $this->id;
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
