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
#[ORM\Index(name: "tech_users_id", columns: ["tech_users_id"])]
#[ORM\Index(name: "cartridgeitemtypes_id", columns: ["cartridgeitemtypes_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "alarm_threshold", columns: ["alarm_threshold"])]
#[ORM\Index(name: "tech_groups_id", columns: ["tech_groups_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class CartridgeItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => false])]
    private $isRecursive;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'ref', type: "string", length: 255, nullable: true)]
    private $ref;


    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;


    #[ORM\ManyToOne(targetEntity: CartridgeItemType::class)]
    #[ORM\JoinColumn(name: 'cartridgeitemtypes_id', referencedColumnName: 'id', nullable: true)]
    private ?CartridgeItemType $cartridgeItemType = null;


    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;


    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;


    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => false])]
    private $isDeleted;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'alarm_threshold', type: "integer", options: ["default" => 10])]
    private $alarmThreshold;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'cartridgeItem', targetEntity: CartridgeItemPrintermodel::class)]
    private Collection $cartridgeItemPrintermodels;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

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
        return $this->alarmThreshold;
    }

    public function setAlarmThreshold(int $alarmThreshold): self
    {
        $this->alarmThreshold = $alarmThreshold;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateModified): self
    {
        $this->dateMod = $dateModified;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreation = $dateCreated;

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
