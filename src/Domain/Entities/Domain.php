<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $domaintypes_id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_expiration;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $users_id_tech;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $groups_id_tech;

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

    function getId(): ?int
    {
        return $this->id;
    }

    function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    function getName(): ?string
    {
        return $this->name;
    }

    function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    function getDomaintypesId(): ?int
    {
        return $this->domaintypes_id;
    }

    function setDomaintypesId(int $domaintypes_id): self
    {
        $this->domaintypes_id = $domaintypes_id;

        return $this;
    }

    function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    function setDateExpiration(\DateTimeInterface $date_expiration): self
    {
        $this->date_expiration = $date_expiration;

        return $this;
    }

    function getUsersIdTech(): ?int
    {
        return $this->users_id_tech;
    }

    function setUsersIdTech(int $users_id_tech): self
    {
        $this->users_id_tech = $users_id_tech;

        return $this;
    }

    function getGroupsIdTech(): ?int
    {
        return $this->groups_id_tech;
    }

    function setGroupsIdTech(int $groups_id_tech): self
    {
        $this->groups_id_tech = $groups_id_tech;

        return $this;
    }

    function getOthers(): ?string
    {
        return $this->others;
    }

    function setOthers(string $others): self
    {
        $this->others = $others;

        return $this;
    }

    function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    function getComment(): ?string
    {
        return $this->comment;
    }

    function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}
