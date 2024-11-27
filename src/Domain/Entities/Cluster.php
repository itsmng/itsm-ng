<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_clusters")]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "clustertypes_id", columns: ["clustertypes_id"])]
#[ORM\Index(name: "autoupdatesystems_id", columns: ["autoupdatesystems_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class Cluster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $version;

    #[ORM\Column(type: "integer", name: "users_id_tech", options: ["default" => 0])]
    private $users_id_tech;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?User $users_tech;

    #[ORM\Column(type: "integer", name: "groups_id_tech", options: ["default" => 0])]
    private $groups_id_tech;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?Group $group_tech;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_deleted;

    #[ORM\Column(type: "integer", options: ["default" => 0, "comment" => "RELATION to states (id)"])]
    private $states_id;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: false)]
    private ?State $state;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", name: "clustertypes_id", options: ["default" => 0])]
    private $clustertypes_id;

    #[ORM\ManyToOne(targetEntity: Clustertype::class)]
    #[ORM\JoinColumn(name: 'clustertypes_id', referencedColumnName: 'id', nullable: false)]
    private ?Clustertype $clustertype;

    #[ORM\Column(type: "integer", name: "autoupdatesystems_id", options: ["default" => 0])]
    private $autoupdatesystems_id;

    #[ORM\ManyToOne(targetEntity: Autoupdatesystem::class)]
    #[ORM\JoinColumn(name: 'autoupdatesystems_id', referencedColumnName: 'id', nullable: false)]
    private ?Autoupdatesystem $autoupdatesystem;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

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

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getClustertypesId(): ?int
    {
        return $this->clustertypes_id;
    }

    public function setClustertypesId(int $clustertypes_id): self
    {
        $this->clustertypes_id = $clustertypes_id;

        return $this;
    }

    public function getAutoUpdateSystemsId(): int
    {
        return $this->autoupdatesystems_id;
    }

    public function setAutoUpdateSystemsId(int $auto_update_systems_id): self
    {
        $this->autoupdatesystems_id = $auto_update_systems_id;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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
     * Get the value of autoupdatesystem
     */
    public function getAutoupdatesystem()
    {
        return $this->autoupdatesystem;
    }

    /**
     * Set the value of autoupdatesystem
     *
     * @return  self
     */
    public function setAutoupdatesystem($autoupdatesystem)
    {
        $this->autoupdatesystem = $autoupdatesystem;

        return $this;
    }

    /**
     * Get the value of clustertype
     */
    public function getClustertype()
    {
        return $this->clustertype;
    }

    /**
     * Set the value of clustertype
     *
     * @return  self
     */
    public function setClustertype($clustertype)
    {
        $this->clustertype = $clustertype;

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
}
