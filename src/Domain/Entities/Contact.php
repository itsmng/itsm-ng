<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_contacts')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'contacttypes_id', columns: ['contacttypes_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'usertitles_id', columns: ['usertitles_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'firstname', type: 'string', length: 255, nullable: true)]
    private $firstname;

    #[ORM\Column(name: 'phone', type: 'string', length: 255, nullable: true)]
    private $phone;

    #[ORM\Column(name: 'phone2', type: 'string', length: 255, nullable: true)]
    private $phone2;

    #[ORM\Column(name: 'mobile', type: 'string', length: 255, nullable: true)]
    private $mobile;

    #[ORM\Column(name: 'fax', type: 'string', length: 255, nullable: true)]
    private $fax;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true)]
    private $email;

    #[ORM\ManyToOne(targetEntity: ContactType::class)]
    #[ORM\JoinColumn(name: 'contacttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?ContactType $contacttype = null;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\ManyToOne(targetEntity: UserTitle::class)]
    #[ORM\JoinColumn(name: 'usertitles_id', referencedColumnName: 'id', nullable: true)]
    private ?UserTitle $usertitle = null;


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

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'contact', targetEntity: ContactSupplier::class)]
    private Collection $contactSuppliers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?int
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(int $isRecursive): self
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone): self
    {
        $this->phone2 = $phone;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $mobile): self
    {
        $this->fax = $mobile;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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

    /**
     * Get the value of entity
     */
    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function getEntityId(): int
    {
        return $this->entity ? $this->entity->getId() : -1;
    }
    /**
     * Set the value of entity
     *
     * @param Entity|null $entity
     * @return self
     */
    public function setEntity(?Entity $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Get the value of usertitle
     */
    public function getUsertitle()
    {
        return $this->usertitle;
    }

    /**
     * Set the value of usertitle
     *
     * @return  self
     */
    public function setUsertitle($usertitle)
    {
        $this->usertitle = $usertitle;

        return $this;
    }

    /**
     * Get the value of contacttype
     */
    public function getContactType()
    {
        return $this->contacttype;
    }

    /**
     * Set the value of contacttype
     *
     * @return  self
     */
    public function setContactType($contacttype)
    {
        $this->contacttype = $contacttype;

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
}
