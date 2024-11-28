<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_devicecontrols')]
#[ORM\Index(name: 'designation', columns: ['designation'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'interfacetypes_id', columns: ['interfacetypes_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'devicecontrolmodels_id', columns: ['devicecontrolmodels_id'])]
class DeviceControl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_raid;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'integer', name: 'manufacturers_id', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'integer', name: 'interfacetypes_id', options: ['default' => 0])]
    private $interfacetypes_id;

    #[ORM\ManyToOne(targetEntity: InterfaceType::class)]
    #[ORM\JoinColumn(name: 'interfacetypes_id', referencedColumnName: 'id', nullable: false)]
    private ?InterfaceType $interfacetype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', name: 'devicecontrolmodels_id', nullable: true)]
    private $devicecontrolmodels_id;

    #[ORM\ManyToOne(targetEntity: DeviceControlModel::class)]
    #[ORM\JoinColumn(name: 'devicecontrolmodels_id', referencedColumnName: 'id', nullable: false)]
    private ?DeviceControlModel $devicecontrolmodel;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

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

    public function getIsRaid(): ?bool
    {
        return $this->is_raid;
    }

    public function setIsRaid(?bool $isRaid): self
    {
        $this->is_raid = $isRaid;

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

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(?int $manufacturersId): self
    {
        $this->manufacturers_id = $manufacturersId;

        return $this;
    }

    public function getInterfacetypesId(): ?int
    {
        return $this->interfacetypes_id;
    }

    public function setInterfacetypesId(?int $interfacetypesId): self
    {
        $this->interfacetypes_id = $interfacetypesId;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entitiesId): self
    {
        $this->entities_id = $entitiesId;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->is_recursive = $isRecursive;

        return $this;
    }

    public function getDevicecontrolmodelsId(): ?int
    {
        return $this->devicecontrolmodels_id;
    }

    public function setDevicecontrolmodelsId(?int $devicecontrolmodelsId): self
    {
        $this->devicecontrolmodels_id = $devicecontrolmodelsId;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->date_mod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->date_creation = $dateCreation;

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
     * Get the value of interfacetype
     */ 
    public function getInterfacetype()
    {
        return $this->interfacetype;
    }

    /**
     * Set the value of interfacetype
     *
     * @return  self
     */ 
    public function setInterfacetype($interfacetype)
    {
        $this->interfacetype = $interfacetype;

        return $this;
    }

    /**
     * Get the value of devicecontrolmodel
     */ 
    public function getDevicecontrolmodel()
    {
        return $this->devicecontrolmodel;
    }

    /**
     * Set the value of devicecontrolmodel
     *
     * @return  self
     */ 
    public function setDevicecontrolmodel($devicecontrolmodel)
    {
        $this->devicecontrolmodel = $devicecontrolmodel;

        return $this;
    }
}
