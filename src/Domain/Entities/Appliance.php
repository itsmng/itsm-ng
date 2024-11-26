<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_appliances')]
#[ORM\UniqueConstraint(name: 'externalidentifier', columns: ['externalidentifier'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'appliancetypes_id', columns: ['appliancetypes_id'])]
#[ORM\Index(name: 'locations_id', columns: ['locations_id'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'applianceenvironments_id', columns: ['applianceenvironments_id'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'users_id_tech', columns: ['users_id_tech'])]
#[ORM\Index(name: 'groups_id', columns: ['groups_id'])]
#[ORM\Index(name: 'groups_id_tech', columns: ['groups_id_tech'])]
#[ORM\Index(name: 'states_id', columns: ['states_id'])]
#[ORM\Index(name: 'serial', columns: ['serial'])]
#[ORM\Index(name: 'otherserial', columns: ['otherserial'])]
#[ORM\Index(name: 'is_helpdesk_visible', columns: ['is_helpdesk_visible'])]

class Appliance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $name;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $appliancetypes_id;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', name: 'locations_id', options: ['default' => 0])]
    private $locations_id;

    #[ORM\ManyToOne(targetEntity: Location::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: false)]
    private ?Location $location;

    #[ORM\Column(type: 'integer', name: 'manufacturers_id', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'integer', name: 'applianceenvironments_id', options: ['default' => 0])]
    private $applianceenvironments_id;

    #[ORM\ManyToOne(targetEntity: ApplianceEnvironment::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'applianceenvionments_id', referencedColumnName: 'id', nullable: false)]
    private ?ApplianceEnvironment $applianceenvironment;

    #[ORM\Column(type: 'integer', name: 'users_id', options: ['default' => 0])]
    private $users_id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user;

    #[ORM\Column(type: 'integer', name: 'users_id_tech', options: ['default' => 0])]
    private $users_id_tech;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?User $user_tech;

    #[ORM\Column(type: 'integer', name: 'groups_id', options: ['default' => 0])]
    private $groups_id;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: false)]
    private ?Group $group;

    #[ORM\Column(type: 'integer', name: 'groups_id_tech', options: ['default' => 0])]
    private $groups_id_tech;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?Group $group_tech;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $date_mod;

    #[ORM\Column(type: 'integer', name: 'states_id', options: ['default' => 0])]
    private $states_id;

    #[ORM\ManyToOne(targetEntity: State::class, inversedBy: 'appliances')]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: false)]
    private ?State $state;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $externalidentifier;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_helpdesk_visible;

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

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getAppliancetypesId(): ?int
    {
        return $this->appliancetypes_id;
    }

    public function setAppliancetypesId(int $appliancetypes_id): self
    {
        $this->appliancetypes_id = $appliancetypes_id;

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

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(int $locations_id): self
    {
        $this->locations_id = $locations_id;
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

    public function getApplianceenvironmentsId(): ?int
    {
        return $this->applianceenvironments_id;
    }

    public function setApplianceenvironmentsId(int $applianceenvironments_id): self
    {
        $this->applianceenvironments_id = $applianceenvironments_id;
        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;
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

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(int $groups_id): self
    {
        $this->groups_id = $groups_id;
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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;
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

    public function getExternalIdentifier(): ?string
    {
        return $this->externalidentifier;
    }

    public function setExternalIdentifier(string $externalidentifier): self
    {
        $this->externalidentifier = $externalidentifier;
        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;
        return $this;
    }

    public function getOtherSerial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherSerial(string $otherserial): self
    {
        $this->otherserial = $otherserial;
        return $this;
    }

    public function getIsHelpdeskVisible(): ?int
    {
        return $this->is_helpdesk_visible;
    }

    public function setIsHelpdeskVisible(int $is_helpdesk_visible): self
    {
        $this->is_helpdesk_visible = $is_helpdesk_visible;
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
     * Get the value of applianceenvironment
     */
    public function getApplianceenvironment()
    {
        return $this->applianceenvironment;
    }

    /**
     * Set the value of applianceenvironment
     *
     * @return  self
     */
    public function setApplianceenvironment($applianceenvironment)
    {
        $this->applianceenvironment = $applianceenvironment;

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
}
