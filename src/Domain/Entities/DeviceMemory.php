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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'designation', type: "string", length: 255, nullable: true)]
    private $designation;

    #[ORM\Column(name: 'frequence', type: "string", length: 255, nullable: true)]
    private $frequence;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'size_default', type: "integer", options: ['default' => 0])]
    private $sizeDefault;

    #[ORM\ManyToOne(targetEntity: DeviceMemoryType::class)]
    #[ORM\JoinColumn(name: 'devicememorytypes_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceMemoryType $deviceMemoryType = null;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ['default' => false])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: DeviceMemoryModel::class)]
    #[ORM\JoinColumn(name: 'devicememorymodels_id', referencedColumnName: 'id', nullable: true)]
    private ?DeviceMemoryModel $deviceMemoryModel = null;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

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
        return $this->sizeDefault;
    }

    public function setSizeDefault(?int $sizeDefault): self
    {
        $this->sizeDefault = $sizeDefault;

        return $this;
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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
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
