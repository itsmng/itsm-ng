<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_calendars')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Calendar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive = 0;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'cache_duration', type: 'text', length: 65535, nullable: true)]
    private $cacheDuration;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'calendar', targetEntity: CalendarHoliday::class)]
    private Collection $calendarHolidays;

    public function __construct()
    {
        $this->calendarHolidays = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

        return $this;
    }

    public function getCacheDuration(): ?string
    {
        return $this->cacheDuration;
    }

    public function setCacheDuration(string $cacheDuration): self
    {
        $this->cacheDuration = $cacheDuration;

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
     * Get the value of calendarHolidays
     */
    public function getCalendarHolidays()
    {
        return $this->calendarHolidays;
    }

    /**
     * Set the value of calendarHolidays
     *
     * @return  self
     */
    public function setCalendarHolidays($calendarHolidays)
    {
        $this->calendarHolidays = $calendarHolidays;

        return $this;
    }
}
