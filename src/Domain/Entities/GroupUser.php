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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'groupUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'groupUsers')]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic = 0;

    #[ORM\Column(name: 'is_manager', type: "boolean", options: ["default" => 0])]
    private $isManager = 0;

    #[ORM\Column(name: 'is_userdelegate', type: "boolean", options: ["default" => 0])]
    private $isUserdelegate = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsDynamic(): ?int
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(int $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getIsManager(): ?int
    {
        return $this->isManager;
    }

    public function setIsManager(int $isManager): self
    {
        $this->isManager = $isManager;

        return $this;
    }

    public function getIsUserdelegate(): ?bool
    {
        return $this->isUserdelegate;
    }

    public function setIsUserdelegate(bool $isUserdelegate): self
    {
        $this->isUserdelegate = $isUserdelegate;

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
