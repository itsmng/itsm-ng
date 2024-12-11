<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_states")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["states_id", "name"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_visible_computer", columns: ["is_visible_computer"])]
#[ORM\Index(name: "is_visible_monitor", columns: ["is_visible_monitor"])]
#[ORM\Index(name: "is_visible_networkequipment", columns: ["is_visible_networkequipment"])]
#[ORM\Index(name: "is_visible_peripheral", columns: ["is_visible_peripheral"])]
#[ORM\Index(name: "is_visible_phone", columns: ["is_visible_phone"])]
#[ORM\Index(name: "is_visible_printer", columns: ["is_visible_printer"])]
#[ORM\Index(name: "is_visible_softwareversion", columns: ["is_visible_softwareversion"])]
#[ORM\Index(name: "is_visible_softwarelicense", columns: ["is_visible_softwarelicense"])]
#[ORM\Index(name: "is_visible_line", columns: ["is_visible_line"])]
#[ORM\Index(name: "is_visible_certificate", columns: ["is_visible_certificate"])]
#[ORM\Index(name: "is_visible_rack", columns: ["is_visible_rack"])]
#[ORM\Index(name: "is_visible_passivedcequipment", columns: ["is_visible_passivedcequipment"])]
#[ORM\Index(name: "is_visible_enclosure", columns: ["is_visible_enclosure"])]
#[ORM\Index(name: "is_visible_pdu", columns: ["is_visible_pdu"])]
#[ORM\Index(name: "is_visible_cluster", columns: ["is_visible_cluster"])]
#[ORM\Index(name: "is_visible_contract", columns: ["is_visible_contract"])]
#[ORM\Index(name: "is_visible_appliance", columns: ["is_visible_appliance"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class State
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;
    
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $completename;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(type: 'text', nullable: true)]
    private $ancestors_cache;

    #[ORM\Column(type: 'text', nullable: true)]
    private $sons_cache;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_computer;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_monitor;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_networkequipment;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_peripheral;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_phone;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_printer;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_softwareversion;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_softwarelicense;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_line;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_certificate;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_rack;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_passivedcequipment;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_enclosure;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_pdu;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_cluster;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_contract;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_visible_appliance;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

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

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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

    public function getCompletename(): ?string
    {
        return $this->completename;
    }

    public function setCompletename(?string $completename): self
    {
        $this->completename = $completename;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getAncestorsCache(): ?string
    {
        return $this->ancestors_cache;
    }

    public function setAncestorsCache(?string $ancestors_cache): self
    {
        $this->ancestors_cache = $ancestors_cache;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sons_cache;
    }

    public function setSonsCache(?string $sons_cache): self
    {
        $this->sons_cache = $sons_cache;

        return $this;
    }

    public function getIsVisibleComputer(): ?bool
    {
        return $this->is_visible_computer;
    }

    public function setIsVisibleComputer(?bool $is_visible_computer): self
    {
        $this->is_visible_computer = $is_visible_computer;

        return $this;
    }

    public function getIsVisibleMonitor(): ?bool
    {
        return $this->is_visible_monitor;
    }

    public function setIsVisibleMonitor(?bool $is_visible_monitor): self
    {
        $this->is_visible_monitor = $is_visible_monitor;

        return $this;
    }

    public function getIsVisibleNetworkequipment(): ?bool
    {
        return $this->is_visible_networkequipment;
    }

    public function setIsVisibleNetworkequipment(?bool $is_visible_networkequipment): self
    {
        $this->is_visible_networkequipment = $is_visible_networkequipment;

        return $this;
    }

    public function getIsVisiblePeripheral(): ?bool
    {
        return $this->is_visible_peripheral;
    }

    public function setIsVisiblePeripheral(?bool $is_visible_peripheral): self
    {
        $this->is_visible_peripheral = $is_visible_peripheral;

        return $this;
    }

    public function getIsVisiblePhone(): ?bool
    {
        return $this->is_visible_phone;
    }

    public function setIsVisiblePhone(?bool $is_visible_phone): self
    {
        $this->is_visible_phone = $is_visible_phone;

        return $this;
    }

    public function getIsVisiblePrinter(): ?bool
    {
        return $this->is_visible_printer;
    }

    public function setIsVisiblePrinter(?bool $is_visible_printer): self
    {
        $this->is_visible_printer = $is_visible_printer;

        return $this;
    }

    public function getIsVisibleSoftwareversion(): ?bool
    {
        return $this->is_visible_softwareversion;
    }

    public function setIsVisibleSoftwareversion(?bool $is_visible_softwareversion): self
    {
        $this->is_visible_softwareversion = $is_visible_softwareversion;

        return $this;
    }

    public function getIsVisibleSoftwarelicense(): ?bool
    {
        return $this->is_visible_softwarelicense;
    }

    public function setIsVisibleSoftwarelicense(?bool $is_visible_softwarelicense): self
    {
        $this->is_visible_softwarelicense = $is_visible_softwarelicense;

        return $this;
    }

    public function getIsVisibleLine(): ?bool
    {
        return $this->is_visible_line;
    }

    public function setIsVisibleLine(?bool $is_visible_line): self
    {
        $this->is_visible_line = $is_visible_line;

        return $this;
    }

    public function getIsVisibleCertificate(): ?bool
    {
        return $this->is_visible_certificate;
    }

    public function setIsVisibleCertificate(?bool $is_visible_certificate): self
    {
        $this->is_visible_certificate = $is_visible_certificate;

        return $this;
    }

    public function getIsVisibleRack(): ?bool
    {
        return $this->is_visible_rack;
    }

    public function setIsVisibleRack(?bool $is_visible_rack): self
    {
        $this->is_visible_rack = $is_visible_rack;

        return $this;
    }

    public function getIsVisiblePassivedcequipment(): ?bool
    {
        return $this->is_visible_passivedcequipment;
    }

    public function setIsVisiblePassivedcequipment(?bool $is_visible_passivedcequipment): self
    {
        $this->is_visible_passivedcequipment = $is_visible_passivedcequipment;

        return $this;
    }

    public function getIsVisibleEnclosure(): ?bool
    {
        return $this->is_visible_enclosure;
    }

    public function setIsVisibleEnclosure(?bool $is_visible_enclosure): self
    {
        $this->is_visible_enclosure = $is_visible_enclosure;

        return $this;
    }

    public function getIsVisiblePdu(): ?bool
    {
        return $this->is_visible_pdu;
    }

    public function setIsVisiblePdu(?bool $is_visible_pdu): self
    {
        $this->is_visible_pdu = $is_visible_pdu;

        return $this;
    }

    public function getIsVisibleCluster(): ?bool
    {
        return $this->is_visible_cluster;
    }

    public function setIsVisibleCluster(?bool $is_visible_cluster): self
    {
        $this->is_visible_cluster = $is_visible_cluster;

        return $this;
    }

    public function getIsVisibleContract(): ?bool
    {
        return $this->is_visible_contract;
    }

    public function setIsVisibleContract(?bool $is_visible_contract): self
    {
        $this->is_visible_contract = $is_visible_contract;

        return $this;
    }

    public function getIsVisibleAppliance(): ?bool
    {
        return $this->is_visible_appliance;
    }

    public function setIsVisibleAppliance(?bool $is_visible_appliance): self
    {
        $this->is_visible_appliance = $is_visible_appliance;

        return $this;
    }

    public function getDateMod(): ?\DateTime
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTime $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTime $date_creation): self
    {
        $this->date_creation = $date_creation;

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
     * Get the value of state
     */ 
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the value of state
     *
     * @return  self
     */ 
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
