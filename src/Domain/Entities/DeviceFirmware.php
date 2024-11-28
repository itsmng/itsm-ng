<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_devicefirmwares")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "devicefirmwaremodels_id", columns: ["devicefirmwaremodels_id"])]
#[ORM\Index(name: "devicefirmwaretypes_id", columns: ["devicefirmwaretypes_id"])]
class DeviceFirmware
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", name: "manufacturers_id", options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: false)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: "date", nullable: true)]
    private $date;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $version;

    #[ORM\Column(type: "integer", name: "devicefirmwaretypes_id", options: ["default" => 0])]
    private $devicefirmwaretypes_id;

    #[ORM\ManyToOne(targetEntity: DeviceFirmwareType::class)]
    #[ORM\JoinColumn(name: 'devicefirmwaretypes_id', referencedColumnName: 'id', nullable: false)]
    private ?DeviceFirmwareType $devicefirmwaretype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", name: "devicefirmwaremodels_id", nullable: true)]
    private $devicefirmwaremodels_id;

    #[ORM\ManyToOne(targetEntity: Devicefirmwaremodel::class)]
    #[ORM\JoinColumn(name: 'devicefirmwaremodels_id', referencedColumnName: 'id', nullable: false)]
    private ?Devicefirmwaremodel $devicefirmwaremodel;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getDeviceFirmwareTypesId(): ?int
    {
        return $this->devicefirmwaretypes_id;
    }

    public function setDeviceFirmwareTypesId(int $devicefirmwaretypes_id): self
    {
        $this->devicefirmwaretypes_id = $devicefirmwaretypes_id;

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

    public function getDeviceFirmwareModelsId(): ?int
    {
        return $this->devicefirmwaremodels_id;
    }

    public function setDeviceFirmwareModelsId(int $devicefirmwaremodels_id): self
    {
        $this->devicefirmwaremodels_id = $devicefirmwaremodels_id;

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
     * Get the value of devicefirmwaretype
     */
    public function getDevicefirmwaretype()
    {
        return $this->devicefirmwaretype;
    }

    /**
     * Set the value of devicefirmwaretype
     *
     * @return  self
     */
    public function setDevicefirmwaretype($devicefirmwaretype)
    {
        $this->devicefirmwaretype = $devicefirmwaretype;

        return $this;
    }

    /**
     * Get the value of devicefirmwaremodel
     */
    public function getDevicefirmwaremodel()
    {
        return $this->devicefirmwaremodel;
    }

    /**
     * Set the value of devicefirmwaremodel
     *
     * @return  self
     */
    public function setDevicefirmwaremodel($devicefirmwaremodel)
    {
        $this->devicefirmwaremodel = $devicefirmwaremodel;

        return $this;
    }
}
