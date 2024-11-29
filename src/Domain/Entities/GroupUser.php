<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_groups_users")]
#[ORM\UniqueConstraint(name: 'unicity', columns: ["users_id", "groups_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "is_manager", columns: ["is_manager"])]
#[ORM\Index(name: "is_userdelegate", columns: ["is_userdelegate"])]
class GroupUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: 'users_id', options: ["default" => 0])]
    private $users_id;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'groupUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user;

    #[ORM\Column(type: "integer", name: 'groups_id', options: ["default" => 0])]
    private $groups_id;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'groupUsers')]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: false)]
    private ?Group $group;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_dynamic;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_manager;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_userdelegate;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
    }

    public function getIsDynamic(): ?int
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(int $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getIsManager(): ?int
    {
        return $this->is_manager;
    }

    public function setIsManager(int $is_manager): self
    {
        $this->is_manager = $is_manager;

        return $this;
    }

    public function getIsUserdelegate(): ?bool
    {
        return $this->is_userdelegate;
    }

    public function setIsUserdelegate(bool $is_userdelegate): self
    {
        $this->is_userdelegate = $is_userdelegate;

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
}
