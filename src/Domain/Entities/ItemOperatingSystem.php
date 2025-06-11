<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_operatingsystems")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["items_id", "itemtype", "operatingsystems_id", "operatingsystemarchitectures_id"])]
#[ORM\Index(name: "items_id", columns: ["items_id"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "operatingsystems_id", columns: ["operatingsystems_id"])]
#[ORM\Index(name: "operatingsystemservicepacks_id", columns: ["operatingsystemservicepacks_id"])]
#[ORM\Index(name: "operatingsystemversions_id", columns: ["operatingsystemversions_id"])]
#[ORM\Index(name: "operatingsystemarchitectures_id", columns: ["operatingsystemarchitectures_id"])]
#[ORM\Index(name: "operatingsystemkernelversions_id", columns: ["operatingsystemkernelversions_id"])]
#[ORM\Index(name: "operatingsystemeditions_id", columns: ["operatingsystemeditions_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class ItemOperatingSystem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId = 0;

    #[ORM\Column(name: 'itemtype', type: "string", length: 255, nullable: true)]
    private $itemtype = null;

    #[ORM\ManyToOne(targetEntity: OperatingSystem::class)]
    #[ORM\JoinColumn(name: 'operatingsystems_id', referencedColumnName: 'id', nullable: true)]
    private ?OperatingSystem $operatingsystem = null;

    #[ORM\ManyToOne(targetEntity: OperatingSystemVersion::class)]
    #[ORM\JoinColumn(name: 'operatingsystemversions_id', referencedColumnName: 'id', nullable: true)]
    private ?OperatingSystemVersion $operatingsystemversion = null;

    #[ORM\ManyToOne(targetEntity: OperatingSystemServicePack::class)]
    #[ORM\JoinColumn(name: 'operatingsystemservicepacks_id', referencedColumnName: 'id', nullable: true)]
    private ?OperatingSystemServicePack $operatingsystemservicepack = null;

    #[ORM\ManyToOne(targetEntity: OperatingSystemArchitecture::class)]
    #[ORM\JoinColumn(name: 'operatingsystemarchitectures_id', referencedColumnName: 'id', nullable: true)]
    private ?OperatingSystemArchitecture $operatingsystemarchitecture = null;

    #[ORM\ManyToOne(targetEntity: OperatingSystemKernelVersion::class)]
    #[ORM\JoinColumn(name: 'operatingsystemkernelversions_id', referencedColumnName: 'id', nullable: true)]
    private ?OperatingSystemKernelVersion $operatingsystemkernelversion = null;

    #[ORM\Column(name: 'license_number', type: "string", length: 255, nullable: true)]
    private $licenseNumber;

    #[ORM\Column(name: 'licenseid', type: "string", length: 255, nullable: true)]
    private $licenseid;

    #[ORM\ManyToOne(targetEntity: OperatingSystemEdition::class)]
    #[ORM\JoinColumn(name: 'operatingsystemeditions_id', referencedColumnName: 'id', nullable: true)]
    private ?OperatingSystemEdition $operatingsystemedition = null;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic = 0;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive = 0;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }


    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(string | null $licenseNumber): self
    {
        $this->licenseNumber = $licenseNumber;

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

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $deleted): self
    {
        $this->isDeleted = $deleted;

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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    /**
     * Get the value of operatingsystem
     */
    public function getOperatingsystem()
    {
        return $this->operatingsystem;
    }

    /**
     * Set the value of operatingsystem
     *
     * @return  self
     */
    public function setOperatingsystem($operatingsystem)
    {
        $this->operatingsystem = $operatingsystem;

        return $this;
    }

    /**
     * Get the value of operatingsystemversion
     */
    public function getOperatingsystemversion()
    {
        return $this->operatingsystemversion;
    }

    /**
     * Set the value of operatingsystemversion
     *
     * @return  self
     */
    public function setOperatingsystemversion($operatingsystemversion)
    {
        $this->operatingsystemversion = $operatingsystemversion;

        return $this;
    }

    /**
     * Get the value of operatingsystemservicepack
     */
    public function getOperatingsystemservicepack()
    {
        return $this->operatingsystemservicepack;
    }

    /**
     * Set the value of operatingsystemservicepack
     *
     * @return  self
     */
    public function setOperatingsystemservicepack($operatingsystemservicepack)
    {
        $this->operatingsystemservicepack = $operatingsystemservicepack;

        return $this;
    }

    /**
     * Get the value of operatingsystemarchitecture
     */
    public function getOperatingsystemarchitecture()
    {
        return $this->operatingsystemarchitecture;
    }

    /**
     * Set the value of operatingsystemarchitecture
     *
     * @return  self
     */
    public function setOperatingsystemarchitecture($operatingsystemarchitecture)
    {
        $this->operatingsystemarchitecture = $operatingsystemarchitecture;

        return $this;
    }

    /**
     * Get the value of licenseid
     */
    public function getLicenseid()
    {
        return $this->licenseid;
    }

    /**
     * Set the value of licenseid
     *
     * @return  self
     */
    public function setLicenseid($licenseid)
    {
        $this->licenseid = $licenseid;

        return $this;
    }

    /**
     * Get the value of operatingsystemkernelversion
     */
    public function getOperatingsystemkernelversion()
    {
        return $this->operatingsystemkernelversion;
    }

    /**
     * Set the value of operatingsystemkernelversion
     *
     * @return  self
     */
    public function setOperatingsystemkernelversion($operatingsystemkernelversion)
    {
        $this->operatingsystemkernelversion = $operatingsystemkernelversion;

        return $this;
    }

    /**
     * Get the value of operatingsystemedition
     */
    public function getOperatingsystemedition()
    {
        return $this->operatingsystemedition;
    }

    /**
     * Set the value of operatingsystemedition
     *
     * @return  self
     */
    public function setOperatingsystemedition($operatingsystemedition)
    {
        $this->operatingsystemedition = $operatingsystemedition;

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
}
