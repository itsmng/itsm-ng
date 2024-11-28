<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_consumableitems')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'locations_id', columns: ['locations_id'])]
#[ORM\Index(name: 'users_id_tech', columns: ['users_id_tech'])]
#[ORM\Index(name: 'consumableitemtypes_id', columns: ['consumableitemtypes_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'alarm_threshold', columns: ['alarm_threshold'])]
#[ORM\Index(name: 'groups_id_tech', columns: ['groups_id_tech'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'otherserial', columns: ['otherserial'])]
class Consumableitem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $ref;

    #[ORM\Column(type: 'integer', name: 'locations_id', options: ['default' => 0])]
    private $locations_id;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: false)]
    private ?Location $location;

    #[ORM\Column(type: 'integer', name: 'consumableitemtypes_id', options: ['default' => 0])]
    private $consumableitemtypes_id;

    #[ORM\ManyToOne(targetEntity: Consumableitemtype::class)]
    #[ORM\JoinColumn(name: 'consumableitemtypes_id', referencedColumnName: 'id', nullable: false)]
    private ?Consumableitemtype $consumableitemtype;

    #[ORM\Column(type: 'integer', name: 'manufacturers_id', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'integer', name: 'users_id_tech', options: ['default' => 0])]
    private $users_id_tech;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?User $users_tech;


    #[ORM\Column(type: 'integer', name: 'groups_id_tech', options: ['default' => 0])]
    private $groups_id_tech;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?Group $groups_tech;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', options: ['default' => 10])]
    private $alarm_threshold;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

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

    public function getIsRecursive(): ?int
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(int $is_recursive): self
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

    public function getConsumableitemtypesId(): ?int
    {
        return $this->consumableitemtypes_id;
    }

    public function setConsumableitemtypesId(int $consumableitemtypes_id): self
    {
        $this->consumableitemtypes_id = $consumableitemtypes_id;

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

    public function getUsersIdTech(): ?int
    {
        return $this->users_id_tech;
    }

    public function setUsersIdTech(int $users_id_tech): self
    {
        $this->users_id_tech = $users_id_tech;

        return $this;
    }

    public function getGroupsIdTech(): ?int
    {
        return $this->groups_id_tech;
    }

    public function setGroupsIdTech(int $groups_id_tech): self
    {
        $this->groups_id_tech = $groups_id_tech;

        return $this;
    }

    public function getIsDeleted(): ?int
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(int $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

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

    public function getOtherserial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherserial(?string $otherserial): self
    {
        $this->otherserial = $otherserial;

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
     * Get the value of consumableitemtype
     */
    public function getConsumableitemtype()
    {
        return $this->consumableitemtype;
    }

    /**
     * Set the value of consumableitemtype
     *
     * @return  self
     */
    public function setConsumableitemtype($consumableitemtype)
    {
        $this->consumableitemtype = $consumableitemtype;

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
     * Get the value of users_tech
     */
    public function getUsers_tech()
    {
        return $this->users_tech;
    }

    /**
     * Set the value of users_tech
     *
     * @return  self
     */
    public function setUsers_tech($users_tech)
    {
        $this->users_tech = $users_tech;

        return $this;
    }

    /**
     * Get the value of groups_tech
     */
    public function getGroups_tech()
    {
        return $this->groups_tech;
    }

    /**
     * Set the value of groups_tech
     *
     * @return  self
     */
    public function setGroups_tech($groups_tech)
    {
        $this->groups_tech = $groups_tech;

        return $this;
    }
}
