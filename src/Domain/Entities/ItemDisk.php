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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $device;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mountpoint;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $filesystems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $totalsize;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $freesize;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_dynamic;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $encryption_status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $encryption_tool;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $encryption_algorithm;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $encryption_type;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime')]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

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
        return $this->filesystems_id;
    }

    public function setFilesystemsId(int $filesystems_id): self
    {
        $this->filesystems_id = $filesystems_id;

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
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getEncryptionStatus(): ?string
    {
        return $this->encryption_status;
    }

    public function setEncryptionStatus(string $encryption_status): self
    {
        $this->encryption_status = $encryption_status;

        return $this;
    }

    public function getEncryptionTool(): ?string
    {
        return $this->encryption_tool;
    }

    public function setEncryptionTool(string $encryption_tool): self
    {
        $this->encryption_tool = $encryption_tool;

        return $this;
    }

    public function getEncryptionAlgorithm(): ?string
    {
        return $this->encryption_algorithm;
    }

    public function setEncryptionAlgorithm(string $encryption_algorithm): self
    {
        $this->encryption_algorithm = $encryption_algorithm;

        return $this;
    }

    public function getEncryptionType(): ?string
    {
        return $this->encryption_type;
    }

    public function setEncryptionType(string $encryption_type): self
    {
        $this->encryption_type = $encryption_type;

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
}
