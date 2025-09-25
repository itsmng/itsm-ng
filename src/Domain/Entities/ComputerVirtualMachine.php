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
class ComputerVirtualMachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\ManyToOne(targetEntity: Computer::class)]
    #[ORM\JoinColumn(name: 'computers_id', referencedColumnName: 'id', nullable: true)]
    private ?Computer $computer = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, options: ['default' => ''])]
    private $name = '';

    #[ORM\ManyToOne(targetEntity: VirtualMachineState::class)]
    #[ORM\JoinColumn(name: 'virtualmachinestates_id', referencedColumnName: 'id', nullable: true)]
    private ?VirtualMachineState $virtualmachinestate = null;

    #[ORM\ManyToOne(targetEntity: VirtualMachineSystem::class)]
    #[ORM\JoinColumn(name: 'virtualmachinesystems_id', referencedColumnName: 'id', nullable: true)]
    private ?VirtualMachineSystem $virtualmachinesystem = null;

    #[ORM\ManyToOne(targetEntity: VirtualMachineType::class)]
    #[ORM\JoinColumn(name: 'virtualmachinetypes_id', referencedColumnName: 'id', nullable: true)]
    private ?VirtualMachineType $virtualmachinetype = null;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, options: ['default' => ''])]
    private $uuid = '';

    #[ORM\Column(name: 'vcpu', type: 'integer', options: ['default' => 0])]
    private $vcpu = 0;

    #[ORM\Column(name: 'ram', type: 'string', length: 255, options: ['default' => ''])]
    private $ram = '';

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => 0])]
    private $isDynamic = 0;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->isDeleted;
    }

    public function setIsDeleted(int $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?int
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(int $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

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

    /**
     * Get the value of computer
     */
    public function getComputer()
    {
        return $this->computer;
    }

    /**
     * Set the value of computer
     *
     * @return  self
     */
    public function setComputer($computer)
    {
        $this->computer = $computer;

        return $this;
    }

    /**
     * Get the value of virtualmachinestate
     */
    public function getVirtualmachinestate()
    {
        return $this->virtualmachinestate;
    }

    /**
     * Set the value of virtualmachinestate
     *
     * @return  self
     */
    public function setVirtualmachinestate($virtualmachinestate)
    {
        $this->virtualmachinestate = $virtualmachinestate;

        return $this;
    }

    /**
     * Get the value of virtualmachinesystem
     */
    public function getVirtualmachinesystem()
    {
        return $this->virtualmachinesystem;
    }

    /**
     * Set the value of virtualmachinesystem
     *
     * @return  self
     */
    public function setVirtualmachinesystem($virtualmachinesystem)
    {
        $this->virtualmachinesystem = $virtualmachinesystem;

        return $this;
    }

    /**
     * Get the value of virtualmachinetype
     */
    public function getVirtualmachinetype()
    {
        return $this->virtualmachinetype;
    }

    /**
     * Set the value of virtualmachinetype
     *
     * @return  self
     */
    public function setVirtualmachinetype($virtualmachinetype)
    {
        $this->virtualmachinetype = $virtualmachinetype;

        return $this;
    }
}
