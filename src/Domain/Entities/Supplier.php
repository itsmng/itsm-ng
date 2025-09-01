<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "glpi_suppliers")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "suppliertypes_id", columns: ["suppliertypes_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
class Supplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive = 0;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: SupplierType::class)]
    #[ORM\JoinColumn(name: 'suppliertypes_id', referencedColumnName: 'id', nullable: true)]
    private ?SupplierType $suppliertype = null;

    #[ORM\Column(name: 'address', type: 'text', length: 65535, nullable: true)]
    private $address;

    #[ORM\Column(name: 'postcode', type: 'string', length: 255, nullable: true)]
    private $postcode;

    #[ORM\Column(name: 'town', type: 'string', length: 255, nullable: true)]
    private $town;

    #[ORM\Column(name: 'state', type: 'string', length: 255, nullable: true)]
    private $state;

    #[ORM\Column(name: 'country', type: 'string', length: 255, nullable: true)]
    private $country;

    #[ORM\Column(name: 'website', type: 'string', length: 255, nullable: true)]
    private $website;

    #[ORM\Column(name: 'phonenumber', type: 'string', length: 255, nullable: true)]
    private $phonenumber;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'fax', type: 'string', length: 255, nullable: true)]
    private $fax;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private $email;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 0])]
    private $isActive = 0;

    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: ChangeSupplier::class)]
    private Collection $changeSuppliers;

    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: ContactSupplier::class)]
    private Collection $contactSuppliers;

    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: ContractSupplier::class)]
    private Collection $contractSuppliers;

    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: ProblemSupplier::class)]
    private Collection $problemSuppliers;

    #[ORM\OneToMany(mappedBy: 'supplier', targetEntity: SupplierTicket::class)]
    private Collection $supplierTickets;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): self
    {
        $this->town = $town;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPhonenumber(): ?string
    {
        return $this->phonenumber;
    }

    public function setPhonenumber(?string $phonenumber): self
    {
        $this->phonenumber = $phonenumber;

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

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }


    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }



    /**
     * Get the value of contactSuppliers
     */
    public function getContactSuppliers()
    {
        return $this->contactSuppliers;
    }

    /**
     * Set the value of contactSuppliers
     *
     * @return  self
     */
    public function setContactSuppliers($contactSuppliers)
    {
        $this->contactSuppliers = $contactSuppliers;

        return $this;
    }

    /**
     * Get the value of contractSuppliers
     */
    public function getContractSuppliers()
    {
        return $this->contractSuppliers;
    }

    /**
     * Set the value of contractSuppliers
     *
     * @return  self
     */
    public function setContractSuppliers($contractSuppliers)
    {
        $this->contractSuppliers = $contractSuppliers;

        return $this;
    }

    /**
     * Get the value of changeSuppliers
     */
    public function getChangeSuppliers()
    {
        return $this->changeSuppliers;
    }

    /**
     * Set the value of changeSuppliers
     *
     * @return  self
     */
    public function setChangeSuppliers($changeSuppliers)
    {
        $this->changeSuppliers = $changeSuppliers;

        return $this;
    }



    /**
     * Get the value of problemSuppliers
     */
    public function getProblemSuppliers()
    {
        return $this->problemSuppliers;
    }

    /**
     * Set the value of problemSuppliers
     *
     * @return  self
     */
    public function setProblemSuppliers($problemSuppliers)
    {
        $this->problemSuppliers = $problemSuppliers;

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
     * Get the value of suppliertype
     */
    public function getSuppliertype()
    {
        return $this->suppliertype;
    }

    /**
     * Set the value of suppliertype
     *
     * @return  self
     */
    public function setSuppliertype($suppliertype)
    {
        $this->suppliertype = $suppliertype;

        return $this;
    }

    /**
     * Get the value of supplierTickets
     */
    public function getSupplierTickets()
    {
        return $this->supplierTickets;
    }

    /**
     * Set the value of supplierTickets
     *
     * @return  self
     */
    public function setSupplierTickets($supplierTickets)
    {
        $this->supplierTickets = $supplierTickets;

        return $this;
    }
}
