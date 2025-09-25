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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'designation', type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'date', type: "date", nullable: true)]
    private $date;

    #[ORM\Column(name: 'version', type: "string", length: 255, nullable: true)]
    private $version;

    #[ORM\ManyToOne(targetEntity: DeviceFirmwareType::class)]
    #[ORM\JoinColumn(name: 'devicefirmwaretypes_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceFirmwareType $devicefirmwaretype = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => false])]
    private $isRecursive = false;

    #[ORM\ManyToOne(targetEntity: DeviceFirmwareModel::class)]
    #[ORM\JoinColumn(name: 'devicefirmwaremodels_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceFirmwareModel $devicefirmwaremodel = null;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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
    public function getDeviceFirmwareModel()
    {
        return $this->devicefirmwaremodel;
    }

    /**
     * Set the value of devicefirmwaremodel
     *
     * @return  self
     */
    public function setDeviceFirmwareModel($devicefirmwaremodel)
    {
        $this->devicefirmwaremodel = $devicefirmwaremodel;

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
