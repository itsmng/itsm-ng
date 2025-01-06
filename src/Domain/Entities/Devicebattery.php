<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_devicebatteries')]
#[ORM\Index(name: 'designation', columns: ['designation'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'devicebatterymodels_id', columns: ['devicebatterymodels_id'])]
#[ORM\Index(name: 'devicebatterytypes_id', columns: ['devicebatterytypes_id'])]
class Devicebattery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;


    #[ORM\Column(type: 'integer', nullable: true)]
    private $voltage;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $capacity;

    #[ORM\ManyToOne(targetEntity: DeviceBatteryType::class)]
    #[ORM\JoinColumn(name: 'devicebatterytypes_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceBatteryType $deviceBatteryType;


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\ManyToOne(targetEntity: Devicebatterymodel::class)]
    #[ORM\JoinColumn(name: 'devicebatterymodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Devicebatterymodel $deviceBatteryModel;


    #[ORM\Column(type: 'datetime', nullable: false)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: false)]
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


    public function getVoltage(): ?int
    {
        return $this->voltage;
    }

    public function setVoltage(int $voltage): self
    {
        $this->voltage = $voltage;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }



    public function getIsRecursive(): ?int
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(int $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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
     * Get the value of deviceBatteryType
     */
    public function getDeviceBatteryType()
    {
        return $this->deviceBatteryType;
    }

    /**
     * Set the value of deviceBatteryType
     *
     * @return  self
     */
    public function setDeviceBatteryType($deviceBatteryType)
    {
        $this->deviceBatteryType = $deviceBatteryType;

        return $this;
    }

    /**
     * Get the value of deviceBatteryModel
     */
    public function getDeviceBatteryModel()
    {
        return $this->deviceBatteryModel;
    }

    /**
     * Set the value of deviceBatteryModel
     *
     * @return  self
     */
    public function setDeviceBatteryModel($deviceBatteryModel)
    {
        $this->deviceBatteryModel = $deviceBatteryModel;

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
