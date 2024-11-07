<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_computerantiviruses")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "antivirus_version", columns: ["antivirus_version"])]
#[ORM\Index(name: "signature_version", columns: ["signature_version"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "is_uptodate", columns: ["is_uptodate"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "computers_id", columns: ["computers_id"])]
#[ORM\Index(name: "date_expiration", columns: ["date_expiration"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class ComputerAntivirus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $computers_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $antivirus_version;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $signature_version;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_active;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_deleted;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_uptodate;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_dynamic;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_expiration;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

        return $this;
    }

    public function getAntivirusVersion(): ?string
    {
        return $this->antivirus_version;
    }

    public function setAntivirusVersion(?string $antivirus_version): self
    {
        $this->antivirus_version = $antivirus_version;

        return $this;
    }

    public function getSignatureVersion(): ?string
    {
        return $this->signature_version;
    }

    public function setSignatureVersion(?string $signature_version): self
    {
        $this->signature_version = $signature_version;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

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

    public function getIsUptodate(): ?bool
    {
        return $this->is_uptodate;
    }

    public function setIsUptodate(?bool $is_uptodate): self
    {
        $this->is_uptodate = $is_uptodate;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(?bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(?\DateTimeInterface $date_expiration): self
    {
        $this->date_expiration = $date_expiration;

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
}
