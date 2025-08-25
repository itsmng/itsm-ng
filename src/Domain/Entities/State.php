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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    #[ORM\Column(name: 'completename', type: 'text', length: 65535, nullable: true)]
    private $completename;

    #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(name: 'ancestors_cache', type: 'text', nullable: true)]
    private $ancestorsCache;

    #[ORM\Column(name: 'sons_cache', type: 'text', nullable: true)]
    private $sonsCache;

    #[ORM\Column(name: 'is_visible_computer', type: 'boolean', options: ['default' => 1])]
    private $isVisibleComputer;

    #[ORM\Column(name: 'is_visible_monitor', type: 'boolean', options: ['default' => 1])]
    private $isVisibleMonitor;

    #[ORM\Column(name: 'is_visible_networkequipment', type: 'boolean', options: ['default' => 1])]
    private $isVisibleNetworkequipment;

    #[ORM\Column(name: 'is_visible_peripheral', type: 'boolean', options: ['default' => 1])]
    private $isVisiblePeripheral;

    #[ORM\Column(name: 'is_visible_phone', type: 'boolean', options: ['default' => 1])]
    private $isVisiblePhone;

    #[ORM\Column(name: 'is_visible_printer', type: 'boolean', options: ['default' => 1])]
    private $isVisiblePrinter;

    #[ORM\Column(name: 'is_visible_softwareversion', type: 'boolean', options: ['default' => 1])]
    private $isVisibleSoftwareversion;

    #[ORM\Column(name: 'is_visible_softwarelicense', type: 'boolean', options: ['default' => 1])]
    private $isVisibleSoftwarelicense;

    #[ORM\Column(name: 'is_visible_line', type: 'boolean', options: ['default' => 1])]
    private $isVisibleLine;

    #[ORM\Column(name: 'is_visible_certificate', type: 'boolean', options: ['default' => 1])]
    private $isVisibleCertificate;

    #[ORM\Column(name: 'is_visible_rack', type: 'boolean', options: ['default' => 1])]
    private $isVisibleRack;

    #[ORM\Column(name: 'is_visible_passivedcequipment', type: 'boolean', options: ['default' => 1])]
    private $isVisiblePassiveDCEquipment;

    #[ORM\Column(name: 'is_visible_enclosure', type: 'boolean', options: ['default' => 1])]
    private $isVisibleEnclosure;

    #[ORM\Column(name: 'is_visible_pdu', type: 'boolean', options: ['default' => 1])]
    private $isVisiblePdu;

    #[ORM\Column(name: 'is_visible_cluster', type: 'boolean', options: ['default' => 1])]
    private $isVisibleCluster;

    #[ORM\Column(name: 'is_visible_contract', type: 'boolean', options: ['default' => 1])]
    private $isVisibleContract;

    #[ORM\Column(name: 'is_visible_appliance', type: 'boolean', options: ['default' => 1])]
    private $isVisibleAppliance;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->ancestorsCache;
    }

    public function setAncestorsCache(?string $ancestorsCache): self
    {
        $this->ancestorsCache = $ancestorsCache;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sonsCache;
    }

    public function setSonsCache(?string $sonsCache): self
    {
        $this->sonsCache = $sonsCache;

        return $this;
    }

    public function getIsVisibleComputer(): ?bool
    {
        return $this->isVisibleComputer;
    }

    public function setIsVisibleComputer(?bool $isVisibleComputer): self
    {
        $this->isVisibleComputer = $isVisibleComputer;

        return $this;
    }

    public function getIsVisibleMonitor(): ?bool
    {
        return $this->isVisibleMonitor;
    }

    public function setIsVisibleMonitor(?bool $isVisibleMonitor): self
    {
        $this->isVisibleMonitor = $isVisibleMonitor;

        return $this;
    }

    public function getIsVisibleNetworkequipment(): ?bool
    {
        return $this->isVisibleNetworkequipment;
    }

    public function setIsVisibleNetworkequipment(?bool $isVisibleNetworkequipment): self
    {
        $this->isVisibleNetworkequipment = $isVisibleNetworkequipment;

        return $this;
    }

    public function getIsVisiblePeripheral(): ?bool
    {
        return $this->isVisiblePeripheral;
    }

    public function setIsVisiblePeripheral(?bool $isVisiblePeripheral): self
    {
        $this->isVisiblePeripheral = $isVisiblePeripheral;

        return $this;
    }

    public function getIsVisiblePhone(): ?bool
    {
        return $this->isVisiblePhone;
    }

    public function setIsVisiblePhone(?bool $isVisiblePhone): self
    {
        $this->isVisiblePhone = $isVisiblePhone;

        return $this;
    }

    public function getIsVisiblePrinter(): ?bool
    {
        return $this->isVisiblePrinter;
    }

    public function setIsVisiblePrinter(?bool $isVisiblePrinter): self
    {
        $this->isVisiblePrinter = $isVisiblePrinter;

        return $this;
    }

    public function getIsVisibleSoftwareversion(): ?bool
    {
        return $this->isVisibleSoftwareversion;
    }

    public function setIsVisibleSoftwareversion(?bool $isVisibleSoftwareversion): self
    {
        $this->isVisibleSoftwareversion = $isVisibleSoftwareversion;

        return $this;
    }

    public function getIsVisibleSoftwarelicense(): ?bool
    {
        return $this->isVisibleSoftwarelicense;
    }

    public function setIsVisibleSoftwarelicense(?bool $isVisibleSoftwarelicense): self
    {
        $this->isVisibleSoftwarelicense = $isVisibleSoftwarelicense;

        return $this;
    }

    public function getIsVisibleLine(): ?bool
    {
        return $this->isVisibleLine;
    }

    public function setIsVisibleLine(?bool $isVisibleLine): self
    {
        $this->isVisibleLine = $isVisibleLine;

        return $this;
    }

    public function getIsVisibleCertificate(): ?bool
    {
        return $this->isVisibleCertificate;
    }

    public function setIsVisibleCertificate(?bool $isVisibleCertificate): self
    {
        $this->isVisibleCertificate = $isVisibleCertificate;

        return $this;
    }

    public function getIsVisibleRack(): ?bool
    {
        return $this->isVisibleRack;
    }

    public function setIsVisibleRack(?bool $isVisibleRack): self
    {
        $this->isVisibleRack = $isVisibleRack;

        return $this;
    }

    public function getIsVisiblePassiveDCEquipment(): ?bool
    {
        return $this->isVisiblePassiveDCEquipment;
    }

    public function setIsVisiblePassiveDCEquipment(?bool $isVisiblePassiveDCEquipment): self
    {
        $this->isVisiblePassiveDCEquipment = $isVisiblePassiveDCEquipment;

        return $this;
    }

    public function getIsVisibleEnclosure(): ?bool
    {
        return $this->isVisibleEnclosure;
    }

    public function setIsVisibleEnclosure(?bool $isVisibleEnclosure): self
    {
        $this->isVisibleEnclosure = $isVisibleEnclosure;

        return $this;
    }

    public function getIsVisiblePdu(): ?bool
    {
        return $this->isVisiblePdu;
    }

    public function setIsVisiblePdu(?bool $isVisiblePdu): self
    {
        $this->isVisiblePdu = $isVisiblePdu;

        return $this;
    }

    public function getIsVisibleCluster(): ?bool
    {
        return $this->isVisibleCluster;
    }

    public function setIsVisibleCluster(?bool $isVisibleCluster): self
    {
        $this->isVisibleCluster = $isVisibleCluster;

        return $this;
    }

    public function getIsVisibleContract(): ?bool
    {
        return $this->isVisibleContract;
    }

    public function setIsVisibleContract(?bool $isVisibleContract): self
    {
        $this->isVisibleContract = $isVisibleContract;

        return $this;
    }

    public function getIsVisibleAppliance(): ?bool
    {
        return $this->isVisibleAppliance;
    }

    public function setIsVisibleAppliance(?bool $isVisibleAppliance): self
    {
        $this->isVisibleAppliance = $isVisibleAppliance;

        return $this;
    }

    public function getDateMod(): ?\DateTime
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTime $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): self
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
