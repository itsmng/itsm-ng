<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_transfers")]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
class Transfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'keep_ticket', type: 'integer', options: ['default' => 0])]
    private $keepTicket = 0;

    #[ORM\Column(name: 'keep_networklink', type: 'integer', options: ['default' => 0])]
    private $keepNetworklink = 0;

    #[ORM\Column(name: 'keep_reservation', type: 'integer', options: ['default' => 0])]
    private $keepReservation = 0;

    #[ORM\Column(name: 'keep_history', type: 'integer', options: ['default' => 0])]
    private $keepHistory = 0;

    #[ORM\Column(name: 'keep_device', type: 'integer', options: ['default' => 0])]
    private $keepDevice = 0;

    #[ORM\Column(name: 'keep_infocom', type: 'integer', options: ['default' => 0])]
    private $keepInfocom = 0;

    #[ORM\Column(name: 'keep_dc_monitor', type: 'integer', options: ['default' => 0])]
    private $keepDcMonitor = 0;

    #[ORM\Column(name: 'clean_dc_monitor', type: 'integer', options: ['default' => 0])]
    private $cleanDcMonitor = 0;

    #[ORM\Column(name: 'keep_dc_phone', type: 'integer', options: ['default' => 0])]
    private $keepDcPhone = 0;

    #[ORM\Column(name: 'clean_dc_phone', type: 'integer', options: ['default' => 0])]
    private $cleanDcPhone = 0;

    #[ORM\Column(name: 'keep_dc_peripheral', type: 'integer', options: ['default' => 0])]
    private $keepDcPeripheral = 0;

    #[ORM\Column(name: 'clean_dc_peripheral', type: 'integer', options: ['default' => 0])]
    private $cleanDcPeripheral = 0;

    #[ORM\Column(name: 'keep_dc_printer', type: 'integer', options: ['default' => 0])]
    private $keepDcPrinter = 0;

    #[ORM\Column(name: 'clean_dc_printer', type: 'integer', options: ['default' => 0])]
    private $cleanDcPrinter = 0;

    #[ORM\Column(name: 'keep_supplier', type: 'integer', options: ['default' => 0])]
    private $keepSupplier = 0;

    #[ORM\Column(name: 'clean_supplier', type: 'integer', options: ['default' => 0])]
    private $cleanSupplier = 0;

    #[ORM\Column(name: 'keep_contact', type: 'integer', options: ['default' => 0])]
    private $keepContact = 0;

    #[ORM\Column(name: 'clean_contact', type: 'integer', options: ['default' => 0])]
    private $cleanContact = 0;

    #[ORM\Column(name: 'keep_contract', type: 'integer', options: ['default' => 0])]
    private $keepContract = 0;

    #[ORM\Column(name: 'clean_contract', type: 'integer', options: ['default' => 0])]
    private $cleanContract = 0;

    #[ORM\Column(name: 'keep_software', type: 'integer', options: ['default' => 0])]
    private $keepSoftware = 0;

    #[ORM\Column(name: 'clean_software', type: 'integer', options: ['default' => 0])]
    private $cleanSoftware = 0;

    #[ORM\Column(name: 'keep_document', type: 'integer', options: ['default' => 0])]
    private $keepDocument = 0;

    #[ORM\Column(name: 'clean_document', type: 'integer', options: ['default' => 0])]
    private $cleanDocument;

    #[ORM\Column(name: 'keep_cartridgeitem', type: 'integer', options: ['default' => 0])]
    private $keepCartridgeitem = 0;

    #[ORM\Column(name: 'clean_cartridgeitem', type: 'integer', options: ['default' => 0])]
    private $cleanCartridgeitem = 0;

    #[ORM\Column(name: 'keep_cartridge', type: 'integer', options: ['default' => 0])]
    private $keepCartridge = 0;

    #[ORM\Column(name: 'keep_consumable', type: 'integer', options: ['default' => 0])]
    private $keepConsumable = 0;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'keep_disk', type: 'integer', options: ['default' => 0])]
    private $keepDisk = 0;

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

    public function getKeepTicket(): ?int
    {
        return $this->keepTicket;
    }

    public function setKeepTicket(?int $keepTicket): self
    {
        $this->keepTicket = $keepTicket;

        return $this;
    }

    public function getKeepNetworklink(): ?int
    {
        return $this->keepNetworklink;
    }

    public function setKeepNetworklink(?int $keepNetworklink): self
    {
        $this->keepNetworklink = $keepNetworklink;

        return $this;
    }

    public function getKeepReservation(): ?int
    {
        return $this->keepReservation;
    }

    public function setKeepReservation(?int $keepReservation): self
    {
        $this->keepReservation = $keepReservation;

        return $this;
    }

    public function getKeepHistory(): ?int
    {
        return $this->keepHistory;
    }

    public function setKeepHistory(?int $keepHistory): self
    {
        $this->keepHistory = $keepHistory;

        return $this;
    }

    public function getKeepDevice(): ?int
    {
        return $this->keepDevice;
    }

    public function setKeepDevice(?int $keepDevice): self
    {
        $this->keepDevice = $keepDevice;

        return $this;
    }

    public function getKeepInfocom(): ?int
    {
        return $this->keepInfocom;
    }

    public function setKeepInfocom(?int $keepInfocom): self
    {
        $this->keepInfocom = $keepInfocom;

        return $this;
    }

    public function getKeepDcMonitor(): ?int
    {
        return $this->keepDcMonitor;
    }

    public function setKeepDcMonitor(?int $keepDcMonitor): self
    {
        $this->keepDcMonitor = $keepDcMonitor;

        return $this;
    }

    public function getCleanDcMonitor(): ?int
    {
        return $this->cleanDcMonitor;
    }

    public function setCleanDcMonitor(?int $cleanDcMonitor): self
    {
        $this->cleanDcMonitor = $cleanDcMonitor;

        return $this;
    }

    public function getKeepDcPhone(): ?int
    {
        return $this->keepDcPhone;
    }

    public function setKeepDcPhone(?int $keepDcPhone): self
    {
        $this->keepDcPhone = $keepDcPhone;

        return $this;
    }

    public function getCleanDcPhone(): ?int
    {
        return $this->cleanDcPhone;
    }

    public function setCleanDcPhone(?int $cleanDcPhone): self
    {
        $this->cleanDcPhone = $cleanDcPhone;

        return $this;
    }

    public function getKeepDcPeripheral(): ?int
    {
        return $this->keepDcPeripheral;
    }

    public function setKeepDcPeripheral(?int $keepDcPeripheral): self
    {
        $this->keepDcPeripheral = $keepDcPeripheral;

        return $this;
    }

    public function getCleanDcPeripheral(): ?int
    {
        return $this->cleanDcPeripheral;
    }

    public function setCleanDcPeripheral(?int $cleanDcPeripheral): self
    {
        $this->cleanDcPeripheral = $cleanDcPeripheral;

        return $this;
    }

    public function getKeepDcPrinter(): ?int
    {
        return $this->keepDcPrinter;
    }

    public function setKeepDcPrinter(?int $keepDcPrinter): self
    {
        $this->keepDcPrinter = $keepDcPrinter;

        return $this;
    }

    public function getCleanDcPrinter(): ?int
    {
        return $this->cleanDcPrinter;
    }

    public function setCleanDcPrinter(?int $cleanDcPrinter): self
    {
        $this->cleanDcPrinter = $cleanDcPrinter;

        return $this;
    }

    public function getKeepSupplier(): ?int
    {
        return $this->keepSupplier;
    }

    public function setKeepSupplier(?int $keepSupplier): self
    {
        $this->keepSupplier = $keepSupplier;

        return $this;
    }

    public function getCleanSupplier(): ?int
    {
        return $this->cleanSupplier;
    }

    public function setCleanSupplier(?int $cleanSupplier): self
    {
        $this->cleanSupplier = $cleanSupplier;

        return $this;
    }

    public function getKeepContact(): ?int
    {
        return $this->keepContact;
    }

    public function setKeepContact(?int $keepContact): self
    {
        $this->keepContact = $keepContact;

        return $this;
    }

    public function getCleanContact(): ?int
    {
        return $this->cleanContact;
    }

    public function setCleanContact(?int $cleanContact): self
    {
        $this->cleanContact = $cleanContact;

        return $this;
    }

    public function getKeepContract(): ?int
    {
        return $this->keepContract;
    }

    public function setKeepContract(?int $keepContract): self
    {
        $this->keepContract = $keepContract;

        return $this;
    }

    public function getCleanContract(): ?int
    {
        return $this->cleanContract;
    }

    public function setCleanContract(?int $cleanContract): self
    {
        $this->cleanContract = $cleanContract;

        return $this;
    }

    public function getKeepSoftware(): ?int
    {
        return $this->keepSoftware;
    }

    public function setKeepSoftware(?int $keepSoftware): self
    {
        $this->keepSoftware = $keepSoftware;

        return $this;
    }

    public function getCleanSoftware(): ?int
    {
        return $this->cleanSoftware;
    }

    public function setCleanSoftware(?int $cleanSoftware): self
    {
        $this->cleanSoftware = $cleanSoftware;

        return $this;
    }

    public function getKeepDocument(): ?int
    {
        return $this->keepDocument;
    }

    public function setKeepDocument(?int $keepDocument): self
    {
        $this->keepDocument = $keepDocument;

        return $this;
    }

    public function getCleanDocument(): ?int
    {
        return $this->cleanDocument;
    }

    public function setCleanDocument(?int $cleanDocument): self
    {
        $this->cleanDocument = $cleanDocument;

        return $this;
    }

    public function getKeepCartridgeitem(): ?int
    {
        return $this->keepCartridgeitem;
    }

    public function setKeepCartridgeitem(?int $keepCartridgeitem): self
    {
        $this->keepCartridgeitem = $keepCartridgeitem;

        return $this;
    }

    public function getCleanCartridgeitem(): ?int
    {
        return $this->cleanCartridgeitem;
    }

    public function setCleanCartridgeitem(?int $cleanCartridgeitem): self
    {
        $this->cleanCartridgeitem = $cleanCartridgeitem;

        return $this;
    }

    public function getKeepCartridge(): ?int
    {
        return $this->keepCartridge;
    }

    public function setKeepCartridge(?int $keepCartridge): self
    {
        $this->keepCartridge = $keepCartridge;

        return $this;
    }

    public function getKeepConsumable(): ?int
    {
        return $this->keepConsumable;
    }

    public function setKeepConsumable(?int $keepConsumable): self
    {
        $this->keepConsumable = $keepConsumable;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getKeepDisk(): ?int
    {
        return $this->keepDisk;
    }

    public function setKeepDisk(?int $keepDisk): self
    {
        $this->keepDisk = $keepDisk;

        return $this;
    }

}
