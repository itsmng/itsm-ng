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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'profileUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'profileUsers')]
    #[ORM\JoinColumn(name: 'profiles_id', referencedColumnName: 'id', nullable: true)]
    private ?Profile $profile = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 1])]
    private $isRecursive;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => 0])]
    private $isDynamic;

    #[ORM\Column(name: 'is_default_profile', type: 'boolean', options: ['default' => 0])]
    private $isDefaultProfile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }


    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }


    public function setIsDynamic(?bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getIsDefaultProfile(): ?bool
    {
        return $this->isDefaultProfile;
    }


    public function setIsDefaultProfile(?bool $isDefaultProfile): self
    {
        $this->isDefaultProfile = $isDefaultProfile;

        return $this;
    }


    /**
     * Get the value of profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set the value of profile
     *
     * @return  self
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

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
    public function setEntity(?Entity $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

}
