<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "glpi_domains")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "domaintypes_id", columns: ["domaintypes_id"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_expiration", columns: ["date_expiration"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Domain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "integer", name: "entities_id", options: ["default" => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", name: "domaintypes_id", options: ["default" => 0])]
    private $domaintypes_id;

    #[ORM\ManyToOne(targetEntity: Domaintype::class)]
    #[ORM\JoinColumn(name: 'domaintypes_id', referencedColumnName: 'id', nullable: false)]
    private ?Domaintype $domaintype;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_expiration;

    #[ORM\Column(type: "integer", name: "users_id_tech", options: ["default" => 0])]
    private $users_id_tech;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?User $userTech;

    #[ORM\Column(type: "integer", name: "groups_id_tech", options: ["default" => 0])]
    private $groups_id_tech;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: false)]
    private ?Group $groupTech;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $others;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_deleted;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'domain', targetEntity: DomainItem::class)]
    private Collection $domainItems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getDomaintypesId(): ?int
    {
        return $this->domaintypes_id;
    }

    public function setDomaintypesId(int $domaintypes_id): self
    {
        $this->domaintypes_id = $domaintypes_id;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(\DateTimeInterface $date_expiration): self
    {
        $this->date_expiration = $date_expiration;

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

    public function getOthers(): ?string
    {
        return $this->others;
    }

    public function setOthers(string $others): self
    {
        $this->others = $others;

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
     * Get the value of domaintype
     */
    public function getDomaintype()
    {
        return $this->domaintype;
    }

    /**
     * Set the value of domaintype
     *
     * @return  self
     */
    public function setDomaintype($domaintype)
    {
        $this->domaintype = $domaintype;

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
     * Get the value of domainItems
     */
    public function getDomainItems()
    {
        return $this->domainItems;
    }

    /**
     * Set the value of domainItems
     *
     * @return  self
     */
    public function setDomainItems($domainItems)
    {
        $this->domainItems = $domainItems;

        return $this;
    }
}
