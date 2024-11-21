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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_ticket;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_networklink;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_reservation;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_history;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_device;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_infocom;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_dc_monitor;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_dc_monitor;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_dc_phone;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_dc_phone;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_dc_peripheral;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_dc_peripheral;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_dc_printer;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_dc_printer;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_supplier;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_supplier;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_contact;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_contact;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_contract;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_contract;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_software;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_software;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_document;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_document;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_cartridgeitem;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $clean_cartridgeitem;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_cartridge;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_consumable;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $keep_disk;

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
        return $this->keep_ticket;
    }

    public function setKeepTicket(?int $keep_ticket): self
    {
        $this->keep_ticket = $keep_ticket;

        return $this;
    }

    public function getKeepNetworklink(): ?int
    {
        return $this->keep_networklink;
    }

    public function setKeepNetworklink(?int $keep_networklink): self
    {
        $this->keep_networklink = $keep_networklink;

        return $this;
    }

    public function getKeepReservation(): ?int
    {
        return $this->keep_reservation;
    }

    public function setKeepReservation(?int $keep_reservation): self
    {
        $this->keep_reservation = $keep_reservation;

        return $this;
    }

    public function getKeepHistory(): ?int
    {
        return $this->keep_history;
    }

    public function setKeepHistory(?int $keep_history): self
    {
        $this->keep_history = $keep_history;

        return $this;
    }

    public function getKeepDevice(): ?int
    {
        return $this->keep_device;
    }

    public function setKeepDevice(?int $keep_device): self
    {
        $this->keep_device = $keep_device;

        return $this;
    }

    public function getKeepInfocom(): ?int
    {
        return $this->keep_infocom;
    }

    public function setKeepInfocom(?int $keep_infocom): self
    {
        $this->keep_infocom = $keep_infocom;

        return $this;
    }

    public function getKeepDcMonitor(): ?int
    {
        return $this->keep_dc_monitor;
    }

    public function setKeepDcMonitor(?int $keep_dc_monitor): self
    {
        $this->keep_dc_monitor = $keep_dc_monitor;

        return $this;
    }

    public function getCleanDcMonitor(): ?int
    {
        return $this->clean_dc_monitor;
    }

    public function setCleanDcMonitor(?int $clean_dc_monitor): self
    {
        $this->clean_dc_monitor = $clean_dc_monitor;

        return $this;
    }

    public function getKeepDcPhone(): ?int
    {
        return $this->keep_dc_phone;
    }

    public function setKeepDcPhone(?int $keep_dc_phone): self
    {
        $this->keep_dc_phone = $keep_dc_phone;

        return $this;
    }

    public function getCleanDcPhone(): ?int
    {
        return $this->clean_dc_phone;
    }

    public function setCleanDcPhone(?int $clean_dc_phone): self
    {
        $this->clean_dc_phone = $clean_dc_phone;

        return $this;
    }

    public function getKeepDcPeripheral(): ?int
    {
        return $this->keep_dc_peripheral;
    }

    public function setKeepDcPeripheral(?int $keep_dc_peripheral): self
    {
        $this->keep_dc_peripheral = $keep_dc_peripheral;

        return $this;
    }

    public function getCleanDcPeripheral(): ?int
    {
        return $this->clean_dc_peripheral;
    }

    public function setCleanDcPeripheral(?int $clean_dc_peripheral): self
    {
        $this->clean_dc_peripheral = $clean_dc_peripheral;

        return $this;
    }

    public function getKeepDcPrinter(): ?int
    {
        return $this->keep_dc_printer;
    }

    public function setKeepDcPrinter(?int $keep_dc_printer): self
    {
        $this->keep_dc_printer = $keep_dc_printer;

        return $this;
    }

    public function getCleanDcPrinter(): ?int
    {
        return $this->clean_dc_printer;
    }

    public function setCleanDcPrinter(?int $clean_dc_printer): self
    {
        $this->clean_dc_printer = $clean_dc_printer;

        return $this;
    }

    public function getKeepSupplier(): ?int
    {
        return $this->keep_supplier;
    }

    public function setKeepSupplier(?int $keep_supplier): self
    {
        $this->keep_supplier = $keep_supplier;

        return $this;
    }

    public function getCleanSupplier(): ?int
    {
        return $this->clean_supplier;
    }

    public function setCleanSupplier(?int $clean_supplier): self
    {
        $this->clean_supplier = $clean_supplier;

        return $this;
    }

    public function getKeepContact(): ?int
    {
        return $this->keep_contact;
    }

    public function setKeepContact(?int $keep_contact): self
    {
        $this->keep_contact = $keep_contact;

        return $this;
    }

    public function getCleanContact(): ?int
    {
        return $this->clean_contact;
    }

    public function setCleanContact(?int $clean_contact): self
    {
        $this->clean_contact = $clean_contact;

        return $this;
    }

    public function getKeepContract(): ?int
    {
        return $this->keep_contract;
    }

    public function setKeepContract(?int $keep_contract): self
    {
        $this->keep_contract = $keep_contract;

        return $this;
    }

    public function getCleanContract(): ?int
    {
        return $this->clean_contract;
    }

    public function setCleanContract(?int $clean_contract): self
    {
        $this->clean_contract = $clean_contract;

        return $this;
    }

    public function getKeepSoftware(): ?int
    {
        return $this->keep_software;
    }

    public function setKeepSoftware(?int $keep_software): self
    {
        $this->keep_software = $keep_software;

        return $this;
    }

    public function getCleanSoftware(): ?int
    {
        return $this->clean_software;
    }

    public function setCleanSoftware(?int $clean_software): self
    {
        $this->clean_software = $clean_software;

        return $this;
    }

    public function getKeepDocument(): ?int
    {
        return $this->keep_document;
    }

    public function setKeepDocument(?int $keep_document): self
    {
        $this->keep_document = $keep_document;

        return $this;
    }

    public function getCleanDocument(): ?int
    {
        return $this->clean_document;
    }

    public function setCleanDocument(?int $clean_document): self
    {
        $this->clean_document = $clean_document;

        return $this;
    }

    public function getKeepCartridgeitem(): ?int
    {
        return $this->keep_cartridgeitem;
    }

    public function setKeepCartridgeitem(?int $keep_cartridgeitem): self
    {
        $this->keep_cartridgeitem = $keep_cartridgeitem;

        return $this;
    }

    public function getCleanCartridgeitem(): ?int
    {
        return $this->clean_cartridgeitem;
    }

    public function setCleanCartridgeitem(?int $clean_cartridgeitem): self
    {
        $this->clean_cartridgeitem = $clean_cartridgeitem;

        return $this;
    }

    public function getKeepCartridge(): ?int
    {
        return $this->keep_cartridge;
    }

    public function setKeepCartridge(?int $keep_cartridge): self
    {
        $this->keep_cartridge = $keep_cartridge;

        return $this;
    }

    public function getKeepConsumable(): ?int
    {
        return $this->keep_consumable;
    }

    public function setKeepConsumable(?int $keep_consumable): self
    {
        $this->keep_consumable = $keep_consumable;

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
        return $this->keep_disk;
    }

    public function setKeepDisk(?int $keep_disk): self
    {
        $this->keep_disk = $keep_disk;

        return $this;
    }

}
