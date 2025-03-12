<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_disks')]
#[ORM\Index(name: "name", columns: ['name'])]
#[ORM\Index(name: "device", columns: ['device'])]
#[ORM\Index(name: "mountpoint", columns: ['mountpoint'])]
#[ORM\Index(name: "totalsize", columns: ['totalsize'])]
#[ORM\Index(name: "freesize", columns: ['freesize'])]
#[ORM\Index(name: "itemtype", columns: ['itemtype'])]
#[ORM\Index(name: "items_id", columns: ['items_id'])]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "filesystems_id", columns: ['filesystems_id'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "is_deleted", columns: ['is_deleted'])]
#[ORM\Index(name: "is_dynamic", columns: ['is_dynamic'])]
#[ORM\Index(name: "date_mod", columns: ['date_mod'])]
#[ORM\Index(name: "date_creation", columns: ['date_creation'])]
class ItemDisk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'entities_id', type: 'integer', options: ['default' => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'device', type: 'string', length: 255, nullable: true)]
    private $device;

    #[ORM\Column(name: 'mountpoint', type: 'string', length: 255, nullable: true)]
    private $mountpoint;

    #[ORM\Column(name: 'filesystems_id', type: 'integer', options: ['default' => 0])]
    private $filesystemsId;

    #[ORM\Column(name: 'totalsize', type: 'integer', options: ['default' => 0])]
    private $totalsize;

    #[ORM\Column(name: 'freesize', type: 'integer', options: ['default' => 0])]
    private $freesize;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => false])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => false])]
    private $isDynamic;

    #[ORM\Column(name: 'encryption_status', type: 'integer', options: ['default' => 0])]
    private $encryptionStatus;

    #[ORM\Column(name: 'encryption_tool', type: 'string', length: 255, nullable: true)]
    private $encryptionTool;

    #[ORM\Column(name: 'encryption_algorithm', type: 'string', length: 255, nullable: true)]
    private $encryptionAlgorithm;

    #[ORM\Column(name: 'encryption_type', type: 'string', length: 255, nullable: true)]
    private $encryptionType;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entitiesId;
    }

    public function setEntitiesId(int $entitiesId): self
    {
        $this->entitiesId = $entitiesId;

        return $this;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getMountpoint(): ?string
    {
        return $this->mountpoint;
    }

    public function setMountpoint(?string $mountpoint): self
    {
        $this->mountpoint = $mountpoint;

        return $this;
    }

    public function getFilesystemsId(): ?int
    {
        return $this->filesystemsId;
    }

    public function setFilesystemsId(int $filesystemsId): self
    {
        $this->filesystemsId = $filesystemsId;

        return $this;
    }

    public function getTotalsize(): ?int
    {
        return $this->totalsize;
    }

    public function setTotalsize(int $totalsize): self
    {
        $this->totalsize = $totalsize;

        return $this;
    }

    public function getFreeSize(): ?int
    {
        return $this->freesize;
    }

    public function setFreeSize(int $freeSize): self
    {
        $this->freesize = $freeSize;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getEncryptionStatus(): ?string
    {
        return $this->encryptionStatus;
    }

    public function setEncryptionStatus(string $encryptionStatus): self
    {
        $this->encryptionStatus = $encryptionStatus;

        return $this;
    }

    public function getEncryptionTool(): ?string
    {
        return $this->encryptionTool;
    }

    public function setEncryptionTool(string | null $encryptionTool): self
    {
        $this->encryptionTool = $encryptionTool;

        return $this;
    }

    public function getEncryptionAlgorithm(): ?string
    {
        return $this->encryptionAlgorithm;
    }

    public function setEncryptionAlgorithm(string | null $encryptionAlgorithm): self
    {
        $this->encryptionAlgorithm = $encryptionAlgorithm;

        return $this;
    }

    public function getEncryptionType(): ?string
    {
        return $this->encryptionType;
    }

    public function setEncryptionType(string | null $encryptionType): self
    {
        $this->encryptionType = $encryptionType;

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
}
