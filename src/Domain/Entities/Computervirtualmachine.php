<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_computervirtualmachines')]
#[ORM\Index(name: 'computers_id', columns: ['computers_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'virtualmachinestates_id', columns: ['virtualmachinestates_id'])]
#[ORM\Index(name: 'virtualmachinesystems_id', columns: ['virtualmachinesystems_id'])]
#[ORM\Index(name: 'vcpu', columns: ['vcpu'])]
#[ORM\Index(name: 'ram', columns: ['ram'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'is_dynamic', columns: ['is_dynamic'])]
#[ORM\Index(name: 'uuid', columns: ['uuid'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Computervirtualmachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $computers_id;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $virtualmachinestates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $virtualmachinesystems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $virtualmachinetypes_id;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $uuid;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $vcpu;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $ram;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_dynamic;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: false)]
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

    public function getComputersId(): ?int
    {
        return $this->computers_id;
    }

    public function setComputersId(int $computers_id): self
    {
        $this->computers_id = $computers_id;

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

    public function getVirtualmachinestatesId(): ?int
    {
        return $this->virtualmachinestates_id;
    }

    public function setVirtualmachinestatesId(int $virtualmachinestates_id): self
    {
        $this->virtualmachinestates_id = $virtualmachinestates_id;

        return $this;
    }

    public function getVirtualmachinesystemsId(): ?int
    {
        return $this->virtualmachinesystems_id;
    }

    public function setVirtualmachinesystemsId(int $virtualmachinesystems_id): self
    {
        $this->virtualmachinesystems_id = $virtualmachinesystems_id;

        return $this;
    }

    public function getVirtualmachinetypesId(): ?int
    {
        return $this->virtualmachinetypes_id;
    }

    public function setVirtualmachinetypesId(int $virtualmachinetypes_id): self
    {
        $this->virtualmachinetypes_id = $virtualmachinetypes_id;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getVcpu(): ?int
    {
        return $this->vcpu;
    }

    public function setVcpu(int $vcpu): self
    {
        $this->vcpu = $vcpu;

        return $this;
    }

    public function getRam(): ?string
    {
        return $this->ram;
    }

    public function setRam(?string $ram): self
    {
        $this->ram = $ram;

        return $this;
    }

    public function getIsDeleted(): ?int
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(int $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?int
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(int $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

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
