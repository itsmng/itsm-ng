<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_planningexternaleventtemplates')]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "state", columns: ["state"])]
#[ORM\Index(name: "planningeventcategories_id", columns: ["planningeventcategories_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Planningexternaleventtemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'text', type: 'text', length: 65535, nullable: true)]
    private $text;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'duration', type: 'integer', options: ['default' => 0])]
    private $duration;

    #[ORM\Column(name: 'before_time', type: 'integer', options: ['default' => 0])]
    private $beforeTime;

    #[ORM\Column(name: 'rrule', type: 'text', length: 65535, nullable: true)]
    private $rrule;

    #[ORM\Column(name: 'state', type: 'integer', options: ['default' => 0])]
    private $state;

    #[ORM\ManyToOne(targetEntity: Planningeventcategory::class)]
    #[ORM\JoinColumn(name: 'planningeventcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Planningeventcategory $planningeventcategory = null;

    #[ORM\Column(name: 'background', type: 'boolean', options: ['default' => 0])]
    private $background;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

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

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getBeforeTime(): ?int
    {
        return $this->beforeTime;
    }

    public function setBeforeTime(?int $beforeTime): self
    {
        $this->beforeTime = $beforeTime;

        return $this;
    }

    public function getRrule(): ?string
    {
        return $this->rrule;
    }

    public function setRrule(?string $rrule): self
    {
        $this->rrule = $rrule;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getBackground(): ?bool
    {
        return $this->background;
    }

    public function setBackground(?bool $background): self
    {
        $this->background = $background;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
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
     * Get the value of planningeventcategory
     */
    public function getPlanningeventcategory()
    {
        return $this->planningeventcategory;
    }

    /**
     * Set the value of planningeventcategory
     *
     * @return  self
     */
    public function setPlanningeventcategory($planningeventcategory)
    {
        $this->planningeventcategory = $planningeventcategory;

        return $this;
    }
}
