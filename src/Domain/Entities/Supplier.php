<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Suppliertype::class)]
    #[ORM\JoinColumn(name: 'suppliertypes_id', referencedColumnName: 'id', nullable: true)]                                                                                                                                                                                                                                                                     
    private ?Suppliertype $suppliertype;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $address;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $postcode;                                                                                                                              

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $town;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $state;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $website;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $phonenumber;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $fax;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $email;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_active;

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
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

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

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): self
    {
        $this->is_active = $is_active;

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
