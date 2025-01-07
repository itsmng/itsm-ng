<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use SoftwareLicense as GlobalSoftwareLicense;

#[ORM\Entity]
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
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "is_helpdesk_visible", columns: ["is_helpdesk_visible"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "allow_overquota", columns: ["allow_overquota"])]
class Softwarelicense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Software::class)]
    #[ORM\JoinColumn(name: 'softwares_id', referencedColumnName: 'id', nullable: true)]
    private ?Software $software;

    #[ORM\ManyToOne(targetEntity: Softwarelicense::class)]
    #[ORM\JoinColumn(name: 'softwarelicenses_id', referencedColumnName: 'id', nullable: true)]
    private ?GlobalSoftwareLicense $softwarelicense;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $completename;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $number;

    #[ORM\ManyToOne(targetEntity: Softwarelicensetype::class)]
    #[ORM\JoinColumn(name: 'softwarelicensetypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Softwarelicensetype $softwarelicensetype;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\ManyToOne(targetEntity: Softwareversion::class)]
    #[ORM\JoinColumn(name: 'softwareversions_id_buy', referencedColumnName: 'id', nullable: true)]
    private ?Softwareversion $softwareversionBuy;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwareversions_id_use;

    #[ORM\ManyToOne(targetEntity: Softwareversion::class)]
    #[ORM\JoinColumn(name: 'softwareversions_id_use', referencedColumnName: 'id', nullable: true)]
    private ?Softwareversion $softwareversionUse;

    #[ORM\Column(type: 'date', nullable: true)]
    private $expire;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_valid;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_helpdesk_visible;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact_num;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $allow_overquota;

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
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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

    public function getSoftwareversionsIdUse(): ?int
    {
        return $this->softwareversions_id_use;
    }

    public function setSoftwareversionsIdUse(?int $softwareversions_id_use): self
    {
        $this->softwareversions_id_use = $softwareversions_id_use;

        return $this;
    }

    public function getExpire(): ?\DateTime
    {
        return $this->expire;
    }

    public function setExpire(?\DateTime $expire): self
    {
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

    public function getDateMod(): ?\DateTime
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTime $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getIsValid(): ?bool
    {
        return $this->is_valid;
    }

    public function setIsValid(?bool $is_valid): self
    {
        $this->is_valid = $is_valid;

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

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(?int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(?int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
    }

    public function getIsHelpdeskVisible(): ?bool
    {
        return $this->is_helpdesk_visible;
    }

    public function setIsHelpdeskVisible(?bool $is_helpdesk_visible): self
    {
        $this->is_helpdesk_visible = $is_helpdesk_visible;

        return $this;
    }

    public function getIsTemplate(): ?bool
    {
        return $this->is_template;
    }

    public function setIsTemplate(?bool $is_template): self
    {
        $this->is_template = $is_template;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->template_name;
    }

    public function setTemplateName(?string $template_name): self
    {
        $this->template_name = $template_name;

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
        return $this->contact_num;
    }

    public function setContactNum(?string $contact_num): self
    {
        $this->contact_num = $contact_num;

        return $this;
    }

    public function getAllowOverquota(): ?bool
    {
        return $this->allow_overquota;
    }

    public function setAllowOverquota(?bool $allow_overquota): self
    {
        $this->allow_overquota = $allow_overquota;

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
    public function getSoftwarelicensetype()
    {
        return $this->softwarelicensetype;
    }

    /**
     * Set the value of softwarelicensetype
     *
     * @return  self
     */
    public function setSoftwarelicensetype($softwarelicensetype)
    {
        $this->softwarelicensetype = $softwarelicensetype;

        return $this;
    }


    /**
     * Get the value of softwareversionBuy
     */
    public function getSoftwareversionBuy()
    {
        return $this->softwareversionBuy;
    }

    /**
     * Set the value of softwareversionBuy
     *
     * @return  self
     */
    public function setSoftwareversionBuy($softwareversionBuy)
    {
        $this->softwareversionBuy = $softwareversionBuy;

        return $this;
    }

    /**
     * Get the value of softwareversionUse
     */
    public function getSoftwareversionUse()
    {
        return $this->softwareversionUse;
    }

    /**
     * Set the value of softwareversionUse
     *
     * @return  self
     */
    public function setSoftwareversionUse($softwareversionUse)
    {
        $this->softwareversionUse = $softwareversionUse;

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
}
