<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "glpi_softwarelicenses")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "serial", columns: ["serial"])]
#[ORM\Index(name: "otherserial", columns: ["otherserial"])]
#[ORM\Index(name: "expire", columns: ["expire"])]
#[ORM\Index(name: "softwareversions_id_buy", columns: ["softwareversions_id_buy"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "softwarelicensetypes_id", columns: ["softwarelicensetypes_id"])]
#[ORM\Index(name: "softwareversions_id_use", columns: ["softwareversions_id_use"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "softwares_id_expire_number", columns: ["softwares_id", "expire", "number"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "tech_users_id", columns: ["tech_users_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "tech_groups_id", columns: ["tech_groups_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "is_helpdesk_visible", columns: ["is_helpdesk_visible"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "allow_overquota", columns: ["allow_overquota"])]
class SoftwareLicense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Software::class)]
    #[ORM\JoinColumn(name: 'softwares_id', referencedColumnName: 'id', nullable: true)]
    private ?Software $software = null;

    #[ORM\ManyToOne(targetEntity: Softwarelicense::class)]
    #[ORM\JoinColumn(name: 'softwarelicenses_id', referencedColumnName: 'id', nullable: true)]
    private ?SoftwareLicense $softwarelicense = null;

    #[ORM\Column(name: 'completename', type: 'text', length: 65535, nullable: true)]
    private $completename;

    #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 0])]
    private $level = 0;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive = false;

    #[ORM\Column(name: 'number', type: 'integer', options: ['default' => 0])]
    private $number = 0;

    #[ORM\ManyToOne(targetEntity: SoftwareLicenseType::class)]
    #[ORM\JoinColumn(name: 'softwarelicensetypes_id', referencedColumnName: 'id', nullable: true)]
    private ?SoftwareLicenseType $softwarelicensetype = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'otherserial', type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\ManyToOne(targetEntity: SoftwareVersion::class)]
    #[ORM\JoinColumn(name: 'softwareversions_id_buy', referencedColumnName: 'id', nullable: true)]
    private ?SoftwareVersion $softwareversionBuy = null;

    #[ORM\ManyToOne(targetEntity: SoftwareVersion::class)]
    #[ORM\JoinColumn(name: 'softwareversions_id_use', referencedColumnName: 'id', nullable: true)]
    private ?SoftwareVersion $softwareversionUse = null;

    #[ORM\Column(name: 'expire', type: 'date', nullable: true)]
    private $expire;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'is_valid', type: 'boolean', options: ['default' => 1])]
    private $isValid = true;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = false;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'groups_id', type: 'integer', options: ['default' => 0])]
    private $groupsId = 0;

    #[ORM\Column(name: 'is_helpdesk_visible', type: 'boolean', options: ['default' => 0])]
    private $isHelpdeskVisible = false;

    #[ORM\Column(name: 'is_template', type: 'boolean', options: ['default' => 0])]
    private $isTemplate = false;

    #[ORM\Column(name: 'template_name', type: 'string', length: 255, nullable: true)]
    private $templateName;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'contact', type: 'string', length: 255, nullable: true)]
    private $contact;

    #[ORM\Column(name: 'contact_num', type: 'string', length: 255, nullable: true)]
    private $contactNum;

    #[ORM\Column(name: 'allow_overquota', type: 'boolean', options: ['default' => 0])]
    private $allowOverquota = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

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

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(?string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getOtherserial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherserial(?string $otherserial): self
    {
        $this->otherserial = $otherserial;

        return $this;
    }



    public function getExpire(): ?\DateTimeInterface
    {
        return $this->expire;
    }

    public function setExpire(\DateTimeInterface|string|null $expire): self
    {
        if (is_string($expire)) {
            $expire = new DateTime($expire);
        }
        $this->expire = $expire;

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

    public function getIsValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(?bool $isValid): self
    {
        $this->isValid = $isValid;

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

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groupsId;
    }

    public function setGroupsId(?int $groupsId): self
    {
        $this->groupsId = $groupsId;

        return $this;
    }

    public function getIsHelpdeskVisible(): ?bool
    {
        return $this->isHelpdeskVisible;
    }

    public function setIsHelpdeskVisible(?bool $isHelpdeskVisible): self
    {
        $this->isHelpdeskVisible = $isHelpdeskVisible;

        return $this;
    }

    public function getIsTemplate(): ?bool
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(?bool $isTemplate): self
    {
        $this->isTemplate = $isTemplate;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getContactNum(): ?string
    {
        return $this->contactNum;
    }

    public function setContactNum(?string $contactNum): self
    {
        $this->contactNum = $contactNum;

        return $this;
    }

    public function getAllowOverquota(): ?bool
    {
        return $this->allowOverquota;
    }

    public function setAllowOverquota(?bool $allowOverquota): self
    {
        $this->allowOverquota = $allowOverquota;

        return $this;
    }


    /**
     * Get the value of software
     */
    public function getSoftware()
    {
        return $this->software;
    }

    /**
     * Set the value of software
     *
     * @return  self
     */
    public function setSoftware($software)
    {
        $this->software = $software;

        return $this;
    }

    /**
     * Get the value of softwarelicense
     */
    public function getSoftwarelicense()
    {
        return $this->softwarelicense;
    }

    /**
     * Set the value of softwarelicense
     *
     * @return  self
     */
    public function setSoftwarelicense($softwarelicense)
    {
        $this->softwarelicense = $softwarelicense;

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
     * Get the value of softwarelicensetype
     */
    public function getSoftwareLicenseType()
    {
        return $this->softwarelicensetype;
    }

    /**
     * Set the value of softwarelicensetype
     *
     * @return  self
     */
    public function setSoftwareLicenseType($softwarelicensetype)
    {
        $this->softwarelicensetype = $softwarelicensetype;

        return $this;
    }


    /**
     * Get the value of softwareversionBuy
     */
    public function getSoftwareVersionBuy()
    {
        return $this->softwareversionBuy;
    }

    /**
     * Set the value of softwareversionBuy
     *
     * @return  self
     */
    public function setSoftwareVersionBuy($softwareversionBuy)
    {
        $this->softwareversionBuy = $softwareversionBuy;

        return $this;
    }


    /**
     * Get the value of location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of techGroup
     */
    public function getTechGroup()
    {
        return $this->techGroup;
    }

    /**
     * Set the value of techGroup
     *
     * @return  self
     */
    public function setTechGroup($techGroup)
    {
        $this->techGroup = $techGroup;

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

    /**
     * Get the value of techUser
     */
    public function getTechUser()
    {
        return $this->techUser;
    }

    /**
     * Set the value of techUser
     *
     * @return  self
     */
    public function setTechUser($techUser)
    {
        $this->techUser = $techUser;

        return $this;
    }

    /**
     * Get the value of softwareversionUse
     */
    public function getSoftwareversionuse()
    {
        return $this->softwareversionUse;
    }

    /**
     * Set the value of softwareversionUse
     *
     * @return  self
     */
    public function setSoftwareversionuse($softwareversionUse)
    {
        $this->softwareversionUse = $softwareversionUse;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
