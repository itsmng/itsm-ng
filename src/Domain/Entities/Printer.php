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
#[ORM\Index(name: "tech_users_id", columns: ["tech_users_id"])]
#[ORM\Index(name: "printertypes_id", columns: ["printertypes_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "tech_groups_id", columns: ["tech_groups_id"])]
#[ORM\Index(name: "last_pages_counter", columns: ["last_pages_counter"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "serial", columns: ["serial"])]
#[ORM\Index(name: "otherserial", columns: ["otherserial"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Printer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options:['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'contact', type: 'string', length: 255, nullable: true)]
    private $contact;

    #[ORM\Column(name: 'contact_num', type: 'string', length: 255, nullable: true)]
    private $contactNum;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'otherserial', type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(name: 'have_serial', type: 'boolean', options:['default' => 0])]
    private $haveSerial;

    #[ORM\Column(name: 'have_parallel', type: 'boolean', options:['default' => 0])]
    private $haveParallel;


    #[ORM\Column(name: 'have_usb', type: 'boolean', options:['default' => 0])]
    private $haveUsb;

    #[ORM\Column(name: 'have_wifi', type: 'boolean', options:['default' => 0])]
    private $haveWifi;

    #[ORM\Column(name: 'have_ethernet', type: 'boolean', options:['default' => 0])]
    private $haveEthernet;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'memory_size', type: 'string', length: 255, nullable: true)]
    private $memorySize;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: Network::class)]
    #[ORM\JoinColumn(name: 'networks_id', referencedColumnName: 'id', nullable: true)]
    private ?Network $network = null;

    #[ORM\ManyToOne(targetEntity: PrinterType::class)]
    #[ORM\JoinColumn(name: 'printertypes_id', referencedColumnName: 'id', nullable: true)]
    private ?PrinterType $printertype = null;

    #[ORM\ManyToOne(targetEntity: PrinterModel::class)]
    #[ORM\JoinColumn(name: 'printermodels_id', referencedColumnName: 'id', nullable: true)]
    private ?PrinterModel $printermodel = null;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'is_global', type: 'boolean', options:['default' => 0])]
    private $isGlobal;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options:['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_template', type: 'boolean', options:['default' => 0])]
    private $isTemplate;

    #[ORM\Column(name: 'template_name', type: 'string', length: 255, nullable: true)]
    private $templateName;

    #[ORM\Column(name: 'init_pages_counter', type: 'integer', options:['default' => 0])]
    private $initPagesCounter;

    #[ORM\Column(name: 'last_pages_counter', type: 'integer', options:['default' => 0])]
    private $lastPagesCounter;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    #[ORM\Column(name: 'ticket_tco', type: 'decimal', precision: 20, scale: 4, nullable: true, options:['default' => "0.0000"])]
    private $ticketTco;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options:['default' => 0])]
    private $isDynamic;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;
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
        return $this->haveSerial;
    }

    public function setHaveSerial(?bool $haveSerial): self
    {
        $this->haveSerial = $haveSerial;
        return $this;
    }

    public function getHaveParallel(): ?bool
    {
        return $this->haveParallel;
    }

    public function setHaveParallel(?bool $haveParallel): self
    {
        $this->haveParallel = $haveParallel;
        return $this;
    }


    public function getHaveUsb(): ?bool
    {
        return $this->haveUsb;
    }

    public function setHaveUsb(?bool $haveUsb): self
    {
        $this->haveUsb = $haveUsb;
        return $this;
    }


    public function getHaveWifi(): ?bool
    {
        return $this->haveWifi;
    }

    public function setHaveWifi(?bool $haveWifi): self
    {
        $this->haveWifi = $haveWifi;
        return $this;
    }


    public function getHaveEthernet(): ?bool
    {
        return $this->haveEthernet;
    }

    public function setHaveEthernet(?bool $haveEthernet): self
    {
        $this->haveEthernet = $haveEthernet;
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
        return $this->memorySize;
    }

    public function setMemorySize(?string $memorySize): self
    {
        $this->memorySize = $memorySize;
        return $this;
    }

    public function getIsGlobal(): ?bool
    {
        return $this->isGlobal;
    }

    public function setIsGlobal(?bool $isGlobal): self
    {
        $this->isGlobal = $isGlobal;
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

    public function getInitPagesCounter(): ?int
    {
        return $this->initPagesCounter;
    }

    public function setInitPagesCounter(?int $initPagesCounter): self
    {
        $this->initPagesCounter = $initPagesCounter;
        return $this;
    }


    public function getLastPagesCounter(): ?int
    {
        return $this->lastPagesCounter;
    }

    public function setLastPagesCounter(?int $lastPagesCounter): self
    {
        $this->lastPagesCounter = $lastPagesCounter;
        return $this;
    }

    public function getTicketTco(): ?float
    {
        return $this->ticketTco;
    }

    public function setTicketTco(?float $ticketTco): self
    {
        $this->ticketTco = $ticketTco;
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
    public function getPrinterType()
    {
        return $this->printertype;
    }

    /**
     * Set the value of printertype
     *
     * @return  self
     */
    public function setPrinterType($printertype)
    {
        $this->printertype = $printertype;

        return $this;
    }

    /**
     * Get the value of printermodel
     */
    public function getPrinterModel()
    {
        return $this->printermodel;
    }

    /**
     * Set the value of printermodel
     *
     * @return  self
     */
    public function setPrinterModel($printermodel)
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
