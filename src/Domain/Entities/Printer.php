<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_printers')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "is_global", columns: ["is_global"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "printermodels_id", columns: ["printermodels_id"])]
#[ORM\Index(name: "networks_id", columns: ["networks_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "printertypes_id", columns: ["printertypes_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "last_pages_counter", columns: ["last_pages_counter"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "serial", columns: ["serial"])]
#[ORM\Index(name: "otherserial", columns: ["otherserial"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Printer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;
    
    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $is_recursive;

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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $have_serial;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $have_parallel;


    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $have_usb;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $have_wifi;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $have_ethernet;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $memory_size;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\ManyToOne(targetEntity: Network::class)]
    #[ORM\JoinColumn(name: 'networks_id', referencedColumnName: 'id', nullable: true)]
    private ?Network $network;

    #[ORM\ManyToOne(targetEntity: Printertype::class)]
    #[ORM\JoinColumn(name: 'printertypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Printertype $printertype;

    #[ORM\ManyToOne(targetEntity: Printermodel::class)]
    #[ORM\JoinColumn(name: 'printermodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Printermodel $printermodel;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $is_global;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\Column(type: 'integer', options:['default' => 0])]
    private $init_pages_counter;

    #[ORM\Column(type: 'integer', options:['default' => 0])]
    private $last_pages_counter;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, nullable: true, options:['default' => 0.0000])]
    private $ticket_tco;

    #[ORM\Column(type: 'boolean', options:['default' => 0])]
    private $is_dynamic;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
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

    public function getHaveSerial(): ?bool
    {
        return $this->have_serial;
    }

    public function setHaveSerial(?bool $have_serial): self
    {
        $this->have_serial = $have_serial;
        return $this;
    }

    public function getHaveParallel(): ?bool
    {
        return $this->have_parallel;
    }

    public function setHaveParallel(?bool $have_parallel): self
    {
        $this->have_parallel = $have_parallel;
        return $this;
    }


    public function getHaveUsb(): ?bool
    {
        return $this->have_usb;
    }

    public function setHaveUsb(?bool $have_usb): self
    {
        $this->have_usb = $have_usb;
        return $this;
    }


    public function getHaveWifi(): ?bool
    {
        return $this->have_wifi;
    }

    public function setHaveWifi(?bool $have_wifi): self
    {
        $this->have_wifi = $have_wifi;
        return $this;
    }


    public function getHaveEthernet(): ?bool
    {
        return $this->have_ethernet;
    }

    public function setHaveEthernet(?bool $have_ethernet): self
    {
        $this->have_ethernet = $have_ethernet;
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

    public function getMemorySize(): ?string
    {
        return $this->memory_size;
    }

    public function setMemorySize(?string $memory_size): self
    {
        $this->memory_size = $memory_size;
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

    public function getInitPagesCounter(): ?int
    {
        return $this->init_pages_counter;
    }

    public function setInitPagesCounter(?int $init_pages_counter): self
    {
        $this->init_pages_counter = $init_pages_counter;
        return $this;
    }


    public function getLastPagesCounter(): ?int
    {
        return $this->last_pages_counter;
    }

    public function setLastPagesCounter(?int $last_pages_counter): self
    {
        $this->last_pages_counter = $last_pages_counter;
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

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
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
     * Get the value of network
     */ 
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Set the value of network
     *
     * @return  self
     */ 
    public function setNetwork($network)
    {
        $this->network = $network;

        return $this;
    }

    /**
     * Get the value of printertype
     */ 
    public function getPrintertype()
    {
        return $this->printertype;
    }

    /**
     * Set the value of printertype
     *
     * @return  self
     */ 
    public function setPrintertype($printertype)
    {
        $this->printertype = $printertype;

        return $this;
    }

    /**
     * Get the value of printermodel
     */ 
    public function getPrintermodel()
    {
        return $this->printermodel;
    }

    /**
     * Set the value of printermodel
     *
     * @return  self
     */ 
    public function setPrintermodel($printermodel)
    {
        $this->printermodel = $printermodel;

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
