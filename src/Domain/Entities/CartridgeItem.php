<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_cartridgeitems")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "cartridgeitemtypes_id", columns: ["cartridgeitemtypes_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "alarm_threshold", columns: ["alarm_threshold"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class CartridgeItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $ref;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $locations_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $cartridgeitemtypes_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $users_id_tech;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $groups_id_tech;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_deleted;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", options: ["default" => 10])]
    private $alarm_threshold;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): self
    {
        $this->ref = $ref;

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

    public function getCartridgeItemTypesId(): ?int
    {
        return $this->cartridgeitemtypes_id;
    }

    public function setCartridgeItemTypesId(int $cartridgeitemtypes_id): self
    {
        $this->cartridgeitemtypes_id = $cartridgeitemtypes_id;

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

    public function getUserIdTech(): ?int
    {
        return $this->user_id_tech;
    }

    public function setUserIdTech(int $user_id_tech): self
    {
        $this->user_id_tech = $user_id_tech;

        return $this;
    }

    public function getGroupIdTech(): ?int
    {
        return $this->group_id_tech;
    }

    public function setGroupIdTech(int $group_id_tech): self
    {
        $this->group_id_tech = $group_id_tech;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAlarmThreshold(): ?int
    {
        return $this->alarm_threshold;
    }

    public function setAlarmThreshold(int $alarm_threshold): self
    {
        $this->alarm_threshold = $alarm_threshold;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_modified): self
    {
        $this->date_mod = $date_modified;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_created): self
    {
        $this->date_creation = $date_created;

        return $this;
    }
}
