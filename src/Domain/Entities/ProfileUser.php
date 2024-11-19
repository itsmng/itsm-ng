<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_profiles_users')]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "profiles_id", columns: ["profiles_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
class ProfileUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $profiles_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_recursive;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_dynamic;
    
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_default_profile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }


    public function setUsersId(?int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getProfilesId(): ?int
    {
        return $this->profiles_id;
    }


    public function setProfilesId(?int $profiles_id): self
    {
        $this->profiles_id = $profiles_id;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }


    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }


    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }


    public function setIsDynamic(?bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getIsDefaultProfile(): ?bool
    {
        return $this->is_default_profile;
    }


    public function setIsDefaultProfile(?bool $is_default_profile): self
    {
        $this->is_default_profile = $is_default_profile;

        return $this;
    }

}