<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_devicesimcards')]
#[ORM\Index(name: 'designation', columns: ['designation'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'devicesimcardtypes_id', columns: ['devicesimcardtypes_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
class Devicesimcard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', name: 'manufacturers_id', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $voltage;

    #[ORM\Column(type: 'integer', name: 'devicesimcardtypes_id', options: ['default' => 0])]
    private $devicesimcardtypes_id;

    #[ORM\ManyToOne(targetEntity: Devicesimcardtype::class)]
    #[ORM\JoinColumn(name: 'devicesimcardtypes_id', referencedColumnName: 'id', nullable: false)]
    private ?Devicesimcardtype $devicesimcardtype;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $allow_voip;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(?int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    public function getVoltage(): ?int
    {
        return $this->voltage;
    }

    public function setVoltage(?int $voltage): self
    {
        $this->voltage = $voltage;

        return $this;
    }

    public function getDevicesimcardtypesId(): ?int
    {
        return $this->devicesimcardtypes_id;
    }

    public function setDevicesimcardtypesId(?int $devicesimcardtypes_id): self
    {
        $this->devicesimcardtypes_id = $devicesimcardtypes_id;

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

    public function getAllowVoip(): ?bool
    {
        return $this->allow_voip;
    }

    public function setAllowVoip(?bool $allow_voip): self
    {
        $this->allow_voip = $allow_voip;

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
     * Get the value of devicesimcardtype
     */ 
    public function getDevicesimcardtype()
    {
        return $this->devicesimcardtype;
    }

    /**
     * Set the value of devicesimcardtype
     *
     * @return  self
     */ 
    public function setDevicesimcardtype($devicesimcardtype)
    {
        $this->devicesimcardtype = $devicesimcardtype;

        return $this;
    }
}
