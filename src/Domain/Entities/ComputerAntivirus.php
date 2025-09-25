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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Computer::class)]
    #[ORM\JoinColumn(name: 'computers_id', referencedColumnName: 'id', nullable: true)]
    private ?Computer $computer = null;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'antivirus_version', type: "string", length: 255, nullable: true)]
    private $antivirusVersion;

    #[ORM\Column(name: 'signature_version', type: "string", length: 255, nullable: true)]
    private $signatureVersion;

    #[ORM\Column(name: 'is_active', type: "boolean", options: ["default" => 0])]
    private $isActive = 0;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'is_uptodate', type: "boolean", options: ["default" => 0])]
    private $isUptodate = 0;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic = 0;

    #[ORM\Column(name: 'date_expiration', type: "datetime", nullable: true)]
    private $dateExpiration;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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


    public function getAntivirusVersion(): ?string
    {
        return $this->antivirusVersion;
    }

    public function setAntivirusVersion(?string $antivirusVersion): self
    {
        $this->antivirusVersion = $antivirusVersion;

        return $this;
    }

    public function getSignatureVersion(): ?string
    {
        return $this->signatureVersion;
    }

    public function setSignatureVersion(?string $signatureVersion): self
    {
        $this->signatureVersion = $signatureVersion;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getIsUptodate(): ?bool
    {
        return $this->isUptodate;
    }

    public function setIsUptodate(?bool $isUptodate): self
    {
        $this->isUptodate = $isUptodate;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(?bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;

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
}
