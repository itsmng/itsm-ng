<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_planningexternalevents')]
#[ORM\UniqueConstraint(name: 'uuid', columns: ['uuid'])]
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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(name: 'planningexternaleventtemplates_id', type: 'integer', options: ['default' => 0])]
    private $planningexternaleventtemplatesId;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 1])]
    private $isRecursive;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(name: 'users_id', type: 'integer', options: ['default' => 0])]
    private $usersId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_guests', referencedColumnName: 'id', nullable: true)]
    private ?User $userGuest = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'text', type: 'text', length: 65535, nullable: true)]
    private $text;

    #[ORM\Column(name: 'begin', type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(name: 'end', type: 'datetime', nullable: true)]
    private $end;

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
        return $this->planningexternaleventtemplatesId;
    }

    public function setPlanningexternaleventtemplatesId(?int $planningexternaleventtemplatesId): self
    {
        $this->planningexternaleventtemplatesId = $planningexternaleventtemplatesId;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->usersId;
    }

    public function setUserId(?int $usersId): self
    {
        $this->usersId = $usersId;

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
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of userGuest
     */
    public function getUserGuest()
    {
        return $this->userGuest;
    }

    /**
     * Set the value of userGuest
     *
     * @return  self
     */
    public function setUserGuest($userGuest)
    {
        $this->userGuest = $userGuest;

        return $this;
    }

    /**
     * Get the value of group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of group
     *
     * @return  self
     */
    public function setGroup($group)
    {
        $this->group = $group;

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
