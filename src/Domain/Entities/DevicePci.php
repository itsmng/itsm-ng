<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_devicepcis")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "devicenetworkcardmodels_id", columns: ["devicenetworkcardmodels_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "devicepcimodels_id", columns: ["devicepcimodels_id"])]
class DevicePci
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", name: 'manufacturer_id', options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $devicenetworkcardmodels_id;

    #[ORM\ManyToOne(targetEntity: Devicenetworkcardmodel::class)]
    #[ORM\JoinColumn(name: 'devicenetworkcardmodels_id', referencedColumnName: 'id', nullable: false)]
    private ?Devicenetworkcardmodel $devicenetworkcardmodel;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", name: 'devicepcimodels_id', nullable: true)]
    private $devicepcimodels_id;

    #[ORM\ManyToOne(targetEntity: Devicepcimodel::class)]
    #[ORM\JoinColumn(name: 'devicepcimodels_id', referencedColumnName: 'id', nullable: false)]
    private ?Devicepcimodel $devicepcimodel;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
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

    public function setManufacturersId(?int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    public function getDeviceNetworkCardModelsId(): ?int
    {
        return $this->devicenetworkcardmodels_id;
    }

    public function setDeviceNetworkCardModelsId(?int $devicenetworkcardmodels_id): self
    {
        $this->devicenetworkcardmodels_id = $devicenetworkcardmodels_id;

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

    public function getDevicePciModelsId(): ?int
    {
        return $this->devicepcimodels_id;
    }

    public function setDevicePciModelsId(?int $devicepcimodels_id): self
    {
        $this->devicepcimodels_id = $devicepcimodels_id;

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
     * Get the value of devicenetworkcardmodel
     */ 
    public function getDevicenetworkcardmodel()
    {
        return $this->devicenetworkcardmodel;
    }

    /**
     * Set the value of devicenetworkcardmodel
     *
     * @return  self
     */ 
    public function setDevicenetworkcardmodel($devicenetworkcardmodel)
    {
        $this->devicenetworkcardmodel = $devicenetworkcardmodel;

        return $this;
    }

    /**
     * Get the value of devicepcimodel
     */ 
    public function getDevicepcimodel()
    {
        return $this->devicepcimodel;
    }

    /**
     * Set the value of devicepcimodel
     *
     * @return  self
     */ 
    public function setDevicepcimodel($devicepcimodel)
    {
        $this->devicepcimodel = $devicepcimodel;

        return $this;
    }
}
