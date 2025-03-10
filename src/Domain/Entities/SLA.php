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
class SLA
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
    private $isRecursive;

    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 0])]
    private $type;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'number_time', type: 'integer')]
    private $numberTime;

    #[ORM\ManyToOne(targetEntity: Calendar::class)]
    #[ORM\JoinColumn(name: 'calendars_id', referencedColumnName: 'id', nullable: true)]
    private ?Calendar $calendar = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'definition_time', type: 'string', length: 255, nullable: true)]
    private $definitionTime;

    #[ORM\Column(name: 'end_of_working_day', type: 'boolean', options: ['default' => 0])]
    private $endOfWorkingDay;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\ManyToOne(targetEntity: Slm::class)]
    #[ORM\JoinColumn(name: 'slms_id', referencedColumnName: 'id', nullable: true)]
    private ?Slm $slm = null;

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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->numberTime;
    }

    public function setNumberTime(int $numberTime): self
    {
        $this->numberTime = $numberTime;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDefinitionTime(): ?string
    {
        return $this->definitionTime;
    }

    public function setDefinitionTime(?string $definitionTime): self
    {
        $this->definitionTime = $definitionTime;

        return $this;
    }

    public function getEndOfWorkingDay(): ?bool
    {
        return $this->endOfWorkingDay;
    }

    public function setEndOfWorkingDay(bool $endOfWorkingDay): self
    {
        $this->endOfWorkingDay = $endOfWorkingDay;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
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
     * Get the value of slm
     */
    public function getSlm()
    {
        return $this->slm;
    }

    /**
     * Set the value of slm
     *
     * @return  self
     */
    public function setSlm($slm)
    {
        $this->slm = $slm;

        return $this;
    }
}
