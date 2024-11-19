<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_planningexternalevents')]
#[ORM\UniqueConstraint(name: 'glpi_planningexternalevents_uc', columns: ['uuid'])]
#[ORM\Index(name: "planningexternaleventtemplates_id", columns: ["planningexternaleventtemplates_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "begin", columns: ["begin"])]
#[ORM\Index(name: "end", columns: ["end"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "state", columns: ["state"])]
#[ORM\Index(name: "planningeventcategories_id", columns: ["planningeventcategories_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Planningexternalevent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $planningexternaleventtemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_recursive;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $users_id_guests;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $text;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $end;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getPlanningexternaleventtemplatesId(): ?int
    {
        return $this->planningexternaleventtemplates_id;
    }

    public function setPlanningexternaleventtemplatesId(?int $planningexternaleventtemplates_id): self
    {
        $this->planningexternaleventtemplates_id = $planningexternaleventtemplates_id;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntities(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUserId(?int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getUsersIdGuests(): ?string
    {
        return $this->users_id_guests;
    }

    public function setUsersIdGuests(?string $users_id_guests): self
    {
        $this->users_id_guests = $users_id_guests;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(?int $groups_id): self
    {
        $this->groups_id = $groups_id;

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

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }   

    public function setBegin(?\DateTimeInterface $begin): self
    {
        $this->begin = $begin;        
        return $this;
    }   

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }       

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;        
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

    public function getplanningeventcategoriesId(): ?int
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

    




