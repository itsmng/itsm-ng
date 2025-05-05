<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_racks')]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "rackmodels_id", columns: ["rackmodels_id"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "racktypes_id", columns: ["racktypes_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "tech_users_id", columns: ["tech_users_id"])]
#[ORM\Index(name: "tech_groups_id", columns: ["tech_groups_id"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "dcrooms_id", columns: ["dcrooms_id"])]
class Rack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive = 0;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'otherserial', type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\ManyToOne(targetEntity: RackModel::class)]
    #[ORM\JoinColumn(name: 'rackmodels_id', referencedColumnName: 'id', nullable: true)]
    private ?RackModel $rackmodel = null;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\ManyToOne(targetEntity: RackType::class)]
    #[ORM\JoinColumn(name: 'racktypes_id', referencedColumnName: 'id', nullable: true)]
    private ?RackType $racktype = null;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'width', type: 'integer', nullable: true)]
    private $width;

    #[ORM\Column(name: 'height', type: 'integer', nullable: true)]
    private $height;

    #[ORM\Column(name: 'depth', type: 'integer', nullable: true)]
    private $depth;

    #[ORM\Column(name: 'number_units', type: 'integer', nullable: true, options: ['default' => 0])]
    private $numberUnits = 0;

    #[ORM\Column(name: 'is_template', type: 'boolean', options: ['default' => 0])]
    private $isTemplate = 0;

    #[ORM\Column(name: 'template_name', type: 'string', length: 255, nullable: true)]
    private $templateName;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\ManyToOne(targetEntity: DCRoom::class)]
    #[ORM\JoinColumn(name: 'dcrooms_id', referencedColumnName: 'id', nullable: true)]
    private ?DCRoom $dcroom = null;

    #[ORM\Column(name: 'room_orientation', type: 'integer', options: ['default' => 0])]
    private $roomOrientation = 0;

    #[ORM\Column(name: 'position', type: 'string', length: 50, nullable: true)]
    private $position;

    #[ORM\Column(name: 'bgcolor', type: 'string', length: 7, nullable: true)]
    private $bgcolor;

    #[ORM\Column(name: 'max_power', type: 'integer', options: ['default' => 0])]
    private $maxPower = 0;

    #[ORM\Column(name: 'mesured_power', type: 'integer', options: ['default' => 0])]
    private $mesuredPower = 0;

    #[ORM\Column(name: 'max_weight', type: 'integer', options: ['default' => 0])]
    private $maxWeight = 0;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

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
        return $this->isRecursive;
    }

    public function setIsRecursive(?string $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->numberUnits;
    }

    public function setNumberUnits(?string $numberUnits): self
    {
        $this->numberUnits = $numberUnits;

        return $this;
    }

    public function getIsTemplate(): ?string
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(?string $isTemplate): self
    {
        $this->isTemplate = $isTemplate;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getIsDeleted(): ?string
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?string $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getRoomOrientation(): ?string
    {
        return $this->roomOrientation;
    }

    public function setRoomOrientation(?string $roomOrientation): self
    {
        $this->roomOrientation = $roomOrientation;

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
        return $this->maxPower;
    }

    public function setMaxPower(?string $maxPower): self
    {
        $this->maxPower = $maxPower;

        return $this;
    }

    public function getMesuredPower(): ?string
    {
        return $this->mesuredPower;
    }

    public function setMesuredPower(?string $mesuredPower): self
    {
        $this->mesuredPower = $mesuredPower;

        return $this;
    }

    public function getMaxWeight(): ?string
    {
        return $this->maxWeight;
    }

    public function setMaxWeight(?string $maxWeight): self
    {
        $this->maxWeight = $maxWeight;

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
    public function getRackModel()
    {
        return $this->rackmodel;
    }

    /**
     * Set the value of rackmodel
     *
     * @return  self
     */
    public function setRackModel($rackmodel)
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
    public function getRackType()
    {
        return $this->racktype;
    }

    /**
     * Set the value of racktype
     *
     * @return  self
     */
    public function setRackType($racktype)
    {
        $this->racktype = $racktype;

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
     * Get the value of dcroom
     */
    public function getDCRoom()
    {
        return $this->dcroom;
    }

    /**
     * Set the value of dcroom
     *
     * @return  self
     */
    public function setDCRoom($dcroom)
    {
        $this->dcroom = $dcroom;

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
