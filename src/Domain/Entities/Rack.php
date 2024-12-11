<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_racks')]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "rackmodels_id", columns: ["rackmodels_id"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "racktypes_id", columns: ["racktypes_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "dcrooms_id", columns: ["dcrooms_id"])]
class Rack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

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

    #[ORM\ManyToOne(targetEntity: Rackmodel::class)]
    #[ORM\JoinColumn(name: 'rackmodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Rackmodel $rackmodel;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\ManyToOne(targetEntity: Racktype::class)]
    #[ORM\JoinColumn(name: 'racktypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Racktype $racktype;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?User $userTech;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?Group $groupTech;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $width;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $height;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $depth;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    private $number_units;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\ManyToOne(targetEntity: Dcroom::class)]
    #[ORM\JoinColumn(name: 'dcrooms_id', referencedColumnName: 'id', nullable: true)]
    private ?Dcroom $dcroom;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $room_orientation;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $position;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private $bgcolor;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $max_power;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $mesured_power;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $max_weight;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'rack', targetEntity: PduRack::class)]
    private Collection $pduRacks;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsRecursive(): ?string
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?string $is_recursive): self
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

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getDepth(): ?string
    {
        return $this->depth;
    }

    public function setDepth(?string $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    public function getNumberUnits(): ?string
    {
        return $this->number_units;
    }

    public function setNumberUnits(?string $number_units): self
    {
        $this->number_units = $number_units;

        return $this;
    }

    public function getIsTemplate(): ?string
    {
        return $this->is_template;
    }

    public function setIsTemplate(?string $is_template): self
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

    public function getIsDeleted(): ?string
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?string $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getRoomOrientation(): ?string
    {
        return $this->room_orientation;
    }

    public function setRoomOrientation(?string $room_orientation): self
    {
        $this->room_orientation = $room_orientation;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getBgcolor(): ?string
    {
        return $this->bgcolor;
    }

    public function setBgcolor(?string $bgcolor): self
    {
        $this->bgcolor = $bgcolor;

        return $this;
    }

    public function getMaxPower(): ?string
    {
        return $this->max_power;
    }

    public function setMaxPower(?string $max_power): self
    {
        $this->max_power = $max_power;

        return $this;
    }

    public function getMesuredPower(): ?string
    {
        return $this->mesured_power;
    }

    public function setMesuredPower(?string $mesured_power): self
    {
        $this->mesured_power = $mesured_power;

        return $this;
    }

    public function getMaxWeight(): ?string
    {
        return $this->max_weight;
    }

    public function setMaxWeight(?string $max_weight): self
    {
        $this->max_weight = $max_weight;

        return $this;
    }

    public function getDateMod(): ?string
    {
        return $this->date_mod;
    }

    public function setDateMod(?string $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->date_creation;
    }

    public function setDateCreation(?string $date_creation): self
    {
        $this->date_creation = $date_creation;

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
     * Get the value of rackmodel
     */ 
    public function getRackmodel()
    {
        return $this->rackmodel;
    }

    /**
     * Set the value of rackmodel
     *
     * @return  self
     */ 
    public function setRackmodel($rackmodel)
    {
        $this->rackmodel = $rackmodel;

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
     * Get the value of racktype
     */ 
    public function getRacktype()
    {
        return $this->racktype;
    }

    /**
     * Set the value of racktype
     *
     * @return  self
     */ 
    public function setRacktype($racktype)
    {
        $this->racktype = $racktype;

        return $this;
    }

    /**
     * Get the value of userTech
     */ 
    public function getUserTech()
    {
        return $this->userTech;
    }

    /**
     * Set the value of userTech
     *
     * @return  self
     */ 
    public function setUserTech($userTech)
    {
        $this->userTech = $userTech;

        return $this;
    }

    /**
     * Get the value of groupTech
     */ 
    public function getGroupTech()
    {
        return $this->groupTech;
    }

    /**
     * Set the value of groupTech
     *
     * @return  self
     */ 
    public function setGroupTech($groupTech)
    {
        $this->groupTech = $groupTech;

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
     * Get the value of dcroom
     */ 
    public function getDcroom()
    {
        return $this->dcroom;
    }

    /**
     * Set the value of dcroom
     *
     * @return  self
     */ 
    public function setDcroom($dcroom)
    {
        $this->dcroom = $dcroom;

        return $this;
    }
}
