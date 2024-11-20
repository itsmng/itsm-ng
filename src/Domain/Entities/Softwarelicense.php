<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

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
Class Softwarelicense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwares_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwarelicenses_id;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $completename;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $number;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwarelicensetypes_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwareversions_id_buy;
    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwareversions_id_use;
    
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

    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_tech;

    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id_tech;

    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]                                
    private $is_helpdesk_visible;

    
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $states_id;

    
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $manufacturers_id;

    
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

    public function getSoftwaresId(): ?int
    {
        return $this->softwares_id;
    }

    public function setSoftwaresId(?int $softwares_id): self
    {
        $this->softwares_id = $softwares_id;

        return $this;
    }

    public function getSoftwarelicensesId(): ?int
    {
        return $this->softwarelicenses_id;
    }

    public function setSoftwarelicensesId(?int $softwarelicenses_id): self
    {
        $this->softwarelicenses_id = $softwarelicenses_id;

        return $this;
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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

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

    public function getSoftwarelicensetypesId(): ?int
    {
        return $this->softwarelicensetypes_id;
    }

    public function setSoftwarelicensetypesId(?int $softwarelicensetypes_id): self
    {
        $this->softwarelicensetypes_id = $softwarelicensetypes_id;

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

    public function getSoftwareversionsIdBuy(): ?int
    {
        return $this->softwareversions_id_buy;
    }

    public function setSoftwareversionsIdBuy(?int $softwareversions_id_buy): self
    {
        $this->softwareversions_id_buy = $softwareversions_id_buy;

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

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
    public function setLocationsId(?int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getUsersIdTech(): ?int
    {
        return $this->users_id_tech;
    }

    public function setUsersIdTech(?int $users_id_tech): self
    {
        $this->users_id_tech = $users_id_tech;

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

    public function getGroupsIdTech(): ?int
    {
        return $this->groups_id_tech;
    }

    public function setGroupsIdTech(?int $groups_id_tech): self
    {
        $this->groups_id_tech = $groups_id_tech;

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

    public function getStatesId(): ?int
    {
        return $this->states_id;
    }

    public function setStatesId(?int $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(?int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

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

}