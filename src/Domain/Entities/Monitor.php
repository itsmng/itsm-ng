<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_monitors')]
#[ORM\Index(name: "name", columns: ['name'])]
#[ORM\Index(name: "is_template", columns: ['is_template'])]
#[ORM\Index(name: "is_global", columns: ['is_global'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "manufacturers_id", columns: ['manufacturers_id'])]
#[ORM\Index(name: "groups_id", columns: ['groups_id'])]
#[ORM\Index(name: "users_id", columns: ['users_id'])]
#[ORM\Index(name: "locations_id", columns: ['locations_id'])]
#[ORM\Index(name: "monitormodels_id", columns: ['monitormodels_id'])]
#[ORM\Index(name: "states_id", columns: ['states_id'])]
#[ORM\Index(name: "tech_users_id", columns: ['tech_users_id'])]
#[ORM\Index(name: "monitortypes_id", columns: ['monitortypes_id'])]
#[ORM\Index(name: "is_deleted", columns: ['is_deleted'])]
#[ORM\Index(name: "tech_groups_id", columns: ['tech_groups_id'])]
#[ORM\Index(name: "is_dynamic", columns: ['is_dynamic'])]
#[ORM\Index(name: "serial", columns: ['serial'])]
#[ORM\Index(name: "otherserial", columns: ['otherserial'])]
#[ORM\Index(name: "date_creation", columns: ['date_creation'])]
#[ORM\Index(name: "is_recursive", columns: ['is_recursive'])]
class Monitor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'contact', type: 'string', length: 255, nullable: true)]
    private $contact = null;

    #[ORM\Column(name: 'contact_num', type: 'string', length: 255, nullable: true)]
    private $contactNum;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'otherserial', type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(name: 'size', type: 'decimal', precision: 5, scale: 2, options: ['default' => "0.00"])]
    private $size = 0.00;

    #[ORM\Column(name: 'have_micro', type: 'boolean', options: ['default' => false])]
    private $haveMicro;

    #[ORM\Column(name: 'have_speaker', type: 'boolean', options: ['default' => false])]
    private $haveSpeaker;

    #[ORM\Column(name: 'have_subd', type: 'boolean', options: ['default' => false])]
    private $haveSubd;

    #[ORM\Column(name: 'have_bnc', type: 'boolean', options: ['default' => false])]
    private $haveBnc;

    #[ORM\Column(name: 'have_dvi', type: 'boolean', options: ['default' => false])]
    private $haveDvi;

    #[ORM\Column(name: 'have_pivot', type: 'boolean', options: ['default' => false])]
    private $havePivot;

    #[ORM\Column(name: 'have_hdmi', type: 'boolean', options: ['default' => false])]
    private $haveHdmi;

    #[ORM\Column(name: 'have_displayport', type: 'boolean', options: ['default' => false])]
    private $haveDisplayport;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: MonitorType::class)]
    #[ORM\JoinColumn(name: 'monitortypes_id', referencedColumnName: 'id', nullable: true)]
    private ?MonitorType $monitortype = null;

    #[ORM\ManyToOne(targetEntity: MonitorModel::class)]
    #[ORM\JoinColumn(name: 'monitormodels_id', referencedColumnName: 'id', nullable: true)]
    private ?MonitorModel $monitormodel = null;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'is_global', type: 'boolean', options: ['default' => 0])]
    private $isGlobal = 0;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'is_template', type: 'boolean', options: ['default' => 0])]
    private $isTemplate = 0;

    #[ORM\Column(name: 'template_name', type: 'string', length: 255, nullable: true)]
    private $templateName;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    #[ORM\Column(name: 'ticket_tco', type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"], nullable: true)]
    private $ticketTco = 0.0000;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => false])]
    private $isDynamic = false;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => false])]
    private $isRecursive = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getOtherserial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherserial(string $otherserial): self
    {
        $this->otherserial = $otherserial;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(float $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getHaveMicro(): ?bool
    {
        return $this->haveMicro;
    }

    public function setHaveMicro(bool $haveMicro): self
    {
        $this->haveMicro = $haveMicro;

        return $this;
    }

    public function getHaveSpeaker(): ?bool
    {
        return $this->haveSpeaker;
    }

    public function setHaveSpeaker(bool $haveSpeaker): self
    {
        $this->haveSpeaker = $haveSpeaker;

        return $this;
    }

    public function getHaveSubd(): ?bool
    {
        return $this->haveSubd;
    }

    public function setHaveSubd(bool $haveSubd): self
    {
        $this->haveSubd = $haveSubd;

        return $this;
    }

    public function getHaveBnc(): ?bool
    {
        return $this->haveBnc;
    }

    public function setHaveBnc(bool $haveBnc): self
    {
        $this->haveBnc = $haveBnc;

        return $this;
    }

    public function getHaveDvi(): ?bool
    {
        return $this->haveDvi;
    }

    public function setHaveDvi(bool $haveDvi): self
    {
        $this->haveDvi = $haveDvi;

        return $this;
    }

    public function getHavePivot(): ?bool
    {
        return $this->havePivot;
    }

    public function setHavePivot(bool $havePivot): self
    {
        $this->havePivot = $havePivot;

        return $this;
    }

    public function getHaveHdmi(): ?bool
    {
        return $this->haveHdmi;
    }

    public function setHaveHdmi(bool $haveHdmi): self
    {
        $this->haveHdmi = $haveHdmi;

        return $this;
    }

    public function getHaveDisplayport(): ?bool
    {
        return $this->haveDisplayport;
    }

    public function setHaveDisplayport(bool $haveDisplayport): self
    {
        $this->haveDisplayport = $haveDisplayport;

        return $this;
    }

    public function getIsGlobal(): ?bool
    {
        return $this->isGlobal;
    }

    public function setIsGlobal(bool $isGlobal): self
    {
        $this->isGlobal = $isGlobal;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsTemplate(): ?bool
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(bool $isTemplate): self
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

    public function getTicketTco(): ?float
    {
        return $this->ticketTco;
    }

    public function setTicketTco(float $ticketTco): self
    {
        $this->ticketTco = $ticketTco;

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

    /**
     * Get the value of monitortype
     */ 
    public function getMonitortype()
    {
        return $this->monitortype;
    }

    /**
     * Set the value of monitortype
     *
     * @return  self
     */ 
    public function setMonitortype($monitortype)
    {
        $this->monitortype = $monitortype;

        return $this;
    }

    /**
     * Get the value of monitormodel
     */ 
    public function getMonitormodel()
    {
        return $this->monitormodel;
    }

    /**
     * Set the value of monitormodel
     *
     * @return  self
     */ 
    public function setMonitormodel($monitormodel)
    {
        $this->monitormodel = $monitormodel;

        return $this;
    }
}
