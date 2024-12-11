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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $rackmodels_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $racktypes_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $states_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_tech;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id_tech;

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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $dcrooms_id;

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

    public function getEntitiesId(): ?string
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?string $entities_id): self
    {
        $this->entities_id = $entities_id;

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

    public function getLocationsId(): ?string
    {
        return $this->locations_id;
    }

    public function setLocationsId(?string $locations_id): self
    {
        $this->locations_id = $locations_id;

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

    public function getRackmodelsId(): ?string
    {
        return $this->rackmodels_id;
    }

    public function setRackmodelsId(?string $rackmodels_id): self
    {
        $this->rackmodels_id = $rackmodels_id;

        return $this;
    }

    public function getManufacturersId(): ?string
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(?string $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    public function getRacktypesId(): ?string
    {
        return $this->racktypes_id;
    }

    public function setRacktypesId(?string $racktypes_id): self
    {
        $this->racktypes_id = $racktypes_id;

        return $this;
    }

    public function getStatesId(): ?string
    {
        return $this->states_id;
    }

    public function setStatesId(?string $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }

    public function getUsersIdTech(): ?string
    {
        return $this->users_id_tech;
    }

    public function setUsersIdTech(?string $users_id_tech): self
    {
        $this->users_id_tech = $users_id_tech;

        return $this;
    }

    public function getGroupsIdTech(): ?string
    {
        return $this->groups_id_tech;
    }

    public function setGroupsIdTech(?string $groups_id_tech): self
    {
        $this->groups_id_tech = $groups_id_tech;

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

    public function getDcroomsId(): ?string
    {
        return $this->dcrooms_id;
    }

    public function setDcroomsId(?string $dcrooms_id): self
    {
        $this->dcrooms_id = $dcrooms_id;

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
}
