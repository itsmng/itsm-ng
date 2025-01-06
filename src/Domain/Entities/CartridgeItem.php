<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

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


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $ref;


    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;


    #[ORM\ManyToOne(targetEntity: CartridgeItemType::class)]
    #[ORM\JoinColumn(name: 'cartridgeitemtypes_id', referencedColumnName: 'id', nullable: true)]
    private ?CartridgeItemType $cartridgeItemType;


    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;


    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?User $user_tech;


    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?Group $group_tech;

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

    #[ORM\OneToMany(mappedBy: 'cartridgeItem', targetEntity: CartridgeItemPrintermodel::class)]
    private Collection $cartridgeItemPrintermodels;

    public function getId(): ?int
    {
        return $this->id;
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
     * Get the value of cartridgeItemType
     */
    public function getCartridgeItemType()
    {
        return $this->cartridgeItemType;
    }

    /**
     * Set the value of cartridgeItemType
     *
     * @return  self
     */
    public function setCartridgeItemType($cartridgeItemType)
    {
        $this->cartridgeItemType = $cartridgeItemType;

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
     * Get the value of user_tech
     */
    public function getUser_tech()
    {
        return $this->user_tech;
    }

    /**
     * Set the value of user_tech
     *
     * @return  self
     */
    public function setUser_tech($user_tech)
    {
        $this->user_tech = $user_tech;

        return $this;
    }

    /**
     * Get the value of group_tech
     */
    public function getGroup_tech()
    {
        return $this->group_tech;
    }

    /**
     * Set the value of group_tech
     *
     * @return  self
     */
    public function setGroup_tech($group_tech)
    {
        $this->group_tech = $group_tech;

        return $this;
    }

    /**
     * Get the value of cartridgeItemPrintermodels
     */
    public function getCartridgeItemPrintermodels()
    {
        return $this->cartridgeItemPrintermodels;
    }

    /**
     * Set the value of cartridgeItemPrintermodels
     *
     * @return  self
     */
    public function setCartridgeItemPrintermodels($cartridgeItemPrintermodels)
    {
        $this->cartridgeItemPrintermodels = $cartridgeItemPrintermodels;

        return $this;
    }
}
