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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $text;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment; 
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $duration;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $before_time;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $rrule;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $state;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $planningeventcategories_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $background;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    
    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
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
        return $this->before_time;
    }   

    public function setBeforeTime(?int $before_time): self
    {
        $this->before_time = $before_time;

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

    public function getPlanningeventcategoriesId(): ?int
    {
        return $this->planningeventcategories_id;
    }

    public function setPlanningeventcategoriesId(?int $planningeventcategories_id): self
    {
        $this->planningeventcategories_id = $planningeventcategories_id;

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
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}