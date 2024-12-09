<?php

namespace Itsmng\Domain\Entities;

use DeviceMemoryModel;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_devicememories")]
#[ORM\Index(name: "designation", columns: ["designation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "devicememorytypes_id", columns: ["devicememorytypes_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "devicememorymodels_id", columns: ["devicememorymodels_id"])]
class DeviceMemory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $frequence;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $size_default;

    #[ORM\ManyToOne(targetEntity: DeviceMemoryType::class)]
    #[ORM\JoinColumn(name: 'devicememorytypes_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceMemoryType $deviceMemoryType;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ['default' => false])]
    private $is_recursive;

    #[ORM\ManyToOne(targetEntity: DeviceMemoryModel::class)]
    #[ORM\JoinColumn(name: 'devicememorymodels_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceMemoryModel $deviceMemoryModel;

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

    public function getFrequence(): ?string
    {
        return $this->frequence;
    }

    public function setFrequence(?string $frequence): self
    {
        $this->frequence = $frequence;

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

    public function getSizeDefault(): ?int
    {
        return $this->size_default;
    }

    public function setSizeDefault(?int $size_default): self
    {
        $this->size_default = $size_default;

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
     * Get the value of deviceMemoryType
     */
    public function getDeviceMemoryType()
    {
        return $this->deviceMemoryType;
    }

    /**
     * Set the value of deviceMemoryType
     *
     * @return  self
     */
    public function setDeviceMemoryType($deviceMemoryType)
    {
        $this->deviceMemoryType = $deviceMemoryType;

        return $this;
    }

    /**
     * Get the value of deviceMemoryModel
     */
    public function getDeviceMemoryModel()
    {
        return $this->deviceMemoryModel;
    }

    /**
     * Set the value of deviceMemoryModel
     *
     * @return  self
     */
    public function setDeviceMemoryModel($deviceMemoryModel)
    {
        $this->deviceMemoryModel = $deviceMemoryModel;

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
