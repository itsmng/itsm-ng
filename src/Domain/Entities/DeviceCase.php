<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_devicecases")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "devicecasetypes_id", columns: ["devicecasetypes_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "devicecasemodels_id", columns: ["devicecasemodels_id"])]
class DeviceCase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: "integer", name: 'devicecasetypes_id', options: ["default" => 0])]
    private $devicecasetypes_id;

    #[ORM\ManyToOne(targetEntity: DevicecaseType::class)]
    #[ORM\JoinColumn(name: 'devicecasetypes_id', referencedColumnName: 'id', nullable: false)]
    private ?DevicecaseType $devicecaseType;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", name: 'manufacturers_id', options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", nullable: true)]
    private $devicecasemodels_id;

    #[ORM\ManyToOne(targetEntity: Devicecasemodel::class)]
    #[ORM\JoinColumn(name: 'devicecasemodels_id', referencedColumnName: 'id', nullable: false)]
    private ?Devicecasemodel $devicecasemodel;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getDevicecasetypesId(): ?int
    {
        return $this->devicecasetypes_id;
    }

    public function setDevicecasetypesId(int $devicecasetypes_id): self
    {
        $this->devicecasetypes_id = $devicecasetypes_id;

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

    public function setManufacturersId(int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

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

    public function getDevicecasemodelsId(): ?int
    {
        return $this->devicecasemodels_id;
    }

    public function setDevicecasemodelsId(?int $devicecasemodels_id): self
    {
        $this->devicecasemodels_id = $devicecasemodels_id;

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
     * Get the value of devicecasemodel
     */ 
    public function getDevicecasemodel()
    {
        return $this->devicecasemodel;
    }

    /**
     * Set the value of devicecasemodel
     *
     * @return  self
     */ 
    public function setDevicecasemodel($devicecasemodel)
    {
        $this->devicecasemodel = $devicecasemodel;

        return $this;
    }

    /**
     * Get the value of devicecaseType
     */ 
    public function getDevicecaseType()
    {
        return $this->devicecaseType;
    }

    /**
     * Set the value of devicecaseType
     *
     * @return  self
     */ 
    public function setDevicecaseType($devicecaseType)
    {
        $this->devicecaseType = $devicecaseType;

        return $this;
    }
}
