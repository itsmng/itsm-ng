<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_phones')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "is_global", columns: ["is_global"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "phonemodels_id", columns: ["phonemodels_id"])]
#[ORM\Index(name: "phonepowersupplies_id", columns: ["phonepowersupplies_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "phonetypes_id", columns: ["phonetypes_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "serial", columns: ["serial"])]
#[ORM\Index(name: "otherserial", columns: ["otherserial"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class Phone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact_num;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?User $userTech;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: true)]
    private ?Group $groupTech;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\ManyToOne(targetEntity: Phonetype::class)]
    #[ORM\JoinColumn(name: 'phonetypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Phonetype $phonetype;

    #[ORM\ManyToOne(targetEntity: Phonemodel::class)]
    #[ORM\JoinColumn(name: 'phonemodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Phonemodel $phonemodel;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $brand;

    #[ORM\ManyToOne(targetEntity: Phonepowersupply::class)]
    #[ORM\JoinColumn(name: 'phonepowersupplies_id', referencedColumnName: 'id', nullable: true)]
    private ?Phonepowersupply $phonepowersupply;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $number_line;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $have_headset;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $have_hp;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_global;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, nullable: true, options: ['default' => 0,0000])]
    private $ticket_tco;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_dynamic;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getNumberLine(): ?string
    {
        return $this->number_line;
    }

    public function setNumberLine(?string $number_line): self
    {
        $this->number_line = $number_line;

        return $this;
    }

    public function getHaveHeadset(): ?bool
    {
        return $this->have_headset;
    }

    public function setHaveHeadset(bool $have_headset): self
    {
        $this->have_headset = $have_headset;

        return $this;
    }

    public function getHaveHp(): ?bool
    {
        return $this->have_hp;
    }

    public function setHaveHp(bool $have_hp): self
    {
        $this->have_hp = $have_hp;

        return $this;
    }

    public function getIsGlobal(): ?bool
    {
        return $this->is_global;
    }

    public function setIsGlobal(?bool $is_global): self
    {
        $this->is_global = $is_global;

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

    public function getTicketTco(): ?float
    {
        return $this->ticket_tco;
    }

    public function setTicketTco(?float $ticket_tco): self
    {
        $this->ticket_tco = $ticket_tco;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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

    /**
     * Get the value of userTech
     */ 
    public function getUserTech()
    {
        return $this->userTech;
    }

    /**
     * Set the value of userTech
     *
     * @return  self
     */ 
    public function setUserTech($userTech)
    {
        $this->userTech = $userTech;

        return $this;
    }

    /**
     * Get the value of groupTech
     */ 
    public function getGroupTech()
    {
        return $this->groupTech;
    }

    /**
     * Set the value of groupTech
     *
     * @return  self
     */ 
    public function setGroupTech($groupTech)
    {
        $this->groupTech = $groupTech;

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
     * Get the value of phonetype
     */ 
    public function getPhonetype()
    {
        return $this->phonetype;
    }

    /**
     * Set the value of phonetype
     *
     * @return  self
     */ 
    public function setPhonetype($phonetype)
    {
        $this->phonetype = $phonetype;

        return $this;
    }

    /**
     * Get the value of phonemodel
     */ 
    public function getPhonemodel()
    {
        return $this->phonemodel;
    }

    /**
     * Set the value of phonemodel
     *
     * @return  self
     */ 
    public function setPhonemodel($phonemodel)
    {
        $this->phonemodel = $phonemodel;

        return $this;
    }

    /**
     * Get the value of phonepowersupply
     */ 
    public function getPhonepowersupply()
    {
        return $this->phonepowersupply;
    }

    /**
     * Set the value of phonepowersupply
     *
     * @return  self
     */ 
    public function setPhonepowersupply($phonepowersupply)
    {
        $this->phonepowersupply = $phonepowersupply;

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
     * Get the value of group
     */ 
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of group
     *
     * @return  self
     */ 
    public function setGroup($group)
    {
        $this->group = $group;

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
}
