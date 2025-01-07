<?php

namespace App\Domain\Entities;

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_pdus')]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "pdumodels_id", columns: ["pdumodels_id"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "group_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "pdutypes_id", columns: ["pdutypes_id"])]
class Pdu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\ManyToOne(targetEntity: Pdumodel::class)]
    #[ORM\JoinColumn(name: 'pdumodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Pdumodel $pdumodel;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_tech;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\ManyToOne(targetEntity: Pdutype::class)]
    #[ORM\JoinColumn(name: 'pdutypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Pdutype $pdutype;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'pdu', targetEntity: PduPlug::class)]
    private Collection $pduPlugs;

    #[ORM\OneToMany(mappedBy: 'pdu', targetEntity: PduRack::class)]
    private Collection $pduRacks;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(?string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getOtherserial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherserial(?string $otherserial): self
    {
        $this->otherserial = $otherserial;

        return $this;
    }

    public function getUsersIdTech(): ?int
    {
        return $this->users_id_tech;
    }

    public function setUsersIdTech(?int $users_id_tech): self
    {
        $this->users_id_tech = $users_id_tech;

        return $this;
    }

    public function getIsTemplate(): ?bool
    {
        return $this->is_template;
    }

    public function setIsTemplate(?bool $is_template): self
    {
        $this->is_template = $is_template;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->template_name;
    }

    public function setTemplateName(?string $template_name): self
    {
        $this->template_name = $template_name;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

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
     * Get the value of location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of pdumodel
     */
    public function getPdumodel()
    {
        return $this->pdumodel;
    }

    /**
     * Set the value of pdumodel
     *
     * @return  self
     */
    public function setPdumodel($pdumodel)
    {
        $this->pdumodel = $pdumodel;

        return $this;
    }


    /**
     * Get the value of techGroup
     */
    public function getTechGroup()
    {
        return $this->techGroup;
    }

    /**
     * Set the value of techGroup
     *
     * @return  self
     */
    public function setTechGroup($techGroup)
    {
        $this->techGroup = $techGroup;

        return $this;
    }

    /**
     * Get the value of state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the value of state
     *
     * @return  self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the value of manufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Set the value of manufacturer
     *
     * @return  self
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Get the value of pdutype
     */
    public function getPdutype()
    {
        return $this->pdutype;
    }

    /**
     * Set the value of pdutype
     *
     * @return  self
     */
    public function setPdutype($pdutype)
    {
        $this->pdutype = $pdutype;

        return $this;
    }

    /**
     * Get the value of pduPlugs
     */
    public function getPduPlugs()
    {
        return $this->pduPlugs;
    }

    /**
     * Set the value of pduPlugs
     *
     * @return  self
     */
    public function setPduPlugs($pduPlugs)
    {
        $this->pduPlugs = $pduPlugs;

        return $this;
    }

    /**
     * Get the value of pduRacks
     */
    public function getPduRacks()
    {
        return $this->pduRacks;
    }

    /**
     * Set the value of pduRacks
     *
     * @return  self
     */
    public function setPduRacks($pduRacks)
    {
        $this->pduRacks = $pduRacks;

        return $this;
    }

    /**
     * Get the value of techUser
     */ 
    public function getTechUser()
    {
        return $this->techUser;
    }

    /**
     * Set the value of techUser
     *
     * @return  self
     */ 
    public function setTechUser($techUser)
    {
        $this->techUser = $techUser;

        return $this;
    }
}
