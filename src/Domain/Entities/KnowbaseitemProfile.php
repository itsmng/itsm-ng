<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_knowbaseitems_profiles')]
#[ORM\Index(name: "knowbaseitems_id", columns: ['knowbaseitems_id'])]
#[ORM\Index(name: "profiles_id", columns: ['profiles_id'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "is_recursive", columns: ['is_recursive'])]
class KnowbaseitemProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitem::class, inversedBy: 'knowbaseitemProfiles')]
    #[ORM\JoinColumn(name: 'knowbaseitems_id', referencedColumnName: 'id', nullable: true)]
    private ?Knowbaseitem $knowbaseitem;

    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'knowbaseitemProfiles')]
    #[ORM\JoinColumn(name: 'profiles_id', referencedColumnName: 'id', nullable: true)]
    private ?Profile $profile;


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;


    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIsRecursive(): bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    /**
     * Get the value of knowbaseitem
     */ 
    public function getKnowbaseitem()
    {
        return $this->knowbaseitem;
    }

    /**
     * Set the value of knowbaseitem
     *
     * @return  self
     */ 
    public function setKnowbaseitem($knowbaseitem)
    {
        $this->knowbaseitem = $knowbaseitem;

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
