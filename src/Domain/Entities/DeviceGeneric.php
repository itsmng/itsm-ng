<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_devicegenerics")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "devicegenerictypes_id", columns: ["devicegenerictypes_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "devicegenericmodels_id", columns: ["devicegenericmodels_id"])]
class DeviceGeneric
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: "integer", name: 'devicegenerictypes_id', options: ["default" => 0])]
    private $devicegenerictypes_id;

    #[ORM\ManyToOne(targetEntity: Devicegenerictype::class)]
    #[ORM\JoinColumn(name: 'devicegenerictypes_id', referencedColumnName: 'id', nullable: false)]
    private ?Devicegenerictype $devicegenerictypes;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", name: 'manufacturers_id', options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: "integer", name: 'entities_id', options: ["default" => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", name: 'locations_id', options: ["default" => 0])]
    private $locations_id;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: false)]
    private ?Location $location;

    #[ORM\Column(type: "integer", name: 'states_is', options: ["default" => 0])]
    private $states_id;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: false)]
    private ?State $state;

    #[ORM\Column(type: "integer", nullable: true)]
    private $devicegenericmodels_id;

    #[ORM\ManyToOne(targetEntity: Devicegenericmodel::class)]
    #[ORM\JoinColumn(name: 'devicegenericmodels_id', referencedColumnName: 'id', nullable: false)]
    private ?Devicegenericmodel $devicegenericmodel;

    #[ORM\Column(type: "datetime", nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: "datetime")]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getDevicegenerictypesId(): ?int
    {
        return $this->devicegenerictypes_id;
    }

    public function setDevicegenerictypesId(int $devicegenerictypes_id): self
    {
        $this->devicegenerictypes_id = $devicegenerictypes_id;

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

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getStatesId(): ?int
    {
        return $this->states_id;
    }

    public function setStatesId(int $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }

    public function getDevicegenericmodelsId(): ?int
    {
        return $this->devicegenericmodels_id;
    }

    public function setDevicegenericmodelsId(int $devicegenericmodels_id): self
    {
        $this->devicegenericmodels_id = $devicegenericmodels_id;

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
     * Get the value of devicegenerictypes
     */ 
    public function getDevicegenerictypes()
    {
        return $this->devicegenerictypes;
    }

    /**
     * Set the value of devicegenerictypes
     *
     * @return  self
     */ 
    public function setDevicegenerictypes($devicegenerictypes)
    {
        $this->devicegenerictypes = $devicegenerictypes;

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
     * Get the value of devicegenericmodel
     */ 
    public function getDevicegenericmodel()
    {
        return $this->devicegenericmodel;
    }

    /**
     * Set the value of devicegenericmodel
     *
     * @return  self
     */ 
    public function setDevicegenericmodel($devicegenericmodel)
    {
        $this->devicegenericmodel = $devicegenericmodel;

        return $this;
    }
}
