<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

//Table: glpi_groups_users
//
//Select data Show structure Alter table New item
//Column	Type	Comment
//id	int(11) Auto Increment
//users_id	int(11) [0]
//groups_id	int(11) [0]
//is_dynamic	tinyint(1) [0]
//is_manager	tinyint(1) [0]
//is_userdelegate	tinyint(1) [0]
//Indexes
//PRIMARY	id
//UNIQUE	users_id, groups_id
//INDEX	groups_id
//INDEX	is_manager
//INDEX	is_userdelegate

#[ORM\Entity]
#[ORM\Table(name: "glpi_groups_users")]
#[ORM\UniqueConstraint(columns: ["users_id", "groups_id"])]
#[ORM\Index(columns: ["groups_id"])]
#[ORM\Index(columns: ["is_manager"])]
#[ORM\Index(columns: ["is_userdelegate"])]
class GroupUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $users_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $groups_id;

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
}
