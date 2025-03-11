<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_computers')]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'is_template', columns: ['is_template'])]
#[ORM\Index(name: 'autoupdatesystems_id', columns: ['autoupdatesystems_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'groups_id', columns: ['groups_id'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'locations_id', columns: ['locations_id'])]
#[ORM\Index(name: 'computermodels_id', columns: ['computermodels_id'])]
#[ORM\Index(name: 'networks_id', columns: ['networks_id'])]
#[ORM\Index(name: 'states_id', columns: ['states_id'])]
#[ORM\Index(name: 'tech_users_id', columns: ['tech_users_id'])]
#[ORM\Index(name: 'computertypes_id', columns: ['computertypes_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'tech_groups_id', columns: ['tech_groups_id'])]
#[ORM\Index(name: 'is_dynamic', columns: ['is_dynamic'])]
#[ORM\Index(name: 'serial', columns: ['serial'])]
#[ORM\Index(name: 'otherserial', columns: ['otherserial'])]
#[ORM\Index(name: 'uuid', columns: ['uuid'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
class Computer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name = null;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial = null;

    #[ORM\Column(name: 'otherserial', type: 'string', length: 255, nullable: true)]
    private $otherserial = null;

    #[ORM\Column(name: 'contact', type: 'string', length: 255, nullable: true)]
    private $contact = null;

    #[ORM\Column(name: 'contact_num', type: 'string', length: 255, nullable: true)]
    private $contactNum = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\ManyToOne(targetEntity: Autoupdatesystem::class)]
    #[ORM\JoinColumn(name: 'autoupdatesystems_id', referencedColumnName: 'id', nullable: true)]
    private ?Autoupdatesystem $autoupdatesystem = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\ManyToOne(targetEntity: Network::class)]
    #[ORM\JoinColumn(name: 'networks_id', referencedColumnName: 'id', nullable: true)]
    private ?Network $network = null;

    #[ORM\ManyToOne(targetEntity: Computermodel::class)]
    #[ORM\JoinColumn(name: 'computermodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Computermodel $computermodel = null;

    #[ORM\ManyToOne(targetEntity: Computertype::class)]
    #[ORM\JoinColumn(name: 'computertypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Computertype $computertype = null;

    #[ORM\Column(name: 'is_template', type: 'boolean', options: ['default' => false])]
    private $isTemplate = false;

    #[ORM\Column(name: 'template_name', type: 'string', length: 255, nullable: true)]
    private $templateName = null;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => false])]
    private $isDeleted = false;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => false])]
    private $isDynamic = false;


    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user =  null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group = null;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    #[ORM\Column(name: 'ticket_tco', type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"], nullable: true)]
    private $ticketTco = null;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, nullable: true)]
    private $uuid = null;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => false])]
    private $isRecursive = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getContactNum(): ?string
    {
        return $this->contactNum;
    }

    public function setContactNum(string $contactNum): self
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

    public function setTemplateName(string $templateName): self
    {
        $this->templateName = $templateName;

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

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

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
    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function getEntityId(): int
    {
        return $this->entity ? $this->entity->getId() : 0;
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
     * Get the value of computermodel
     */
    public function getComputermodel()
    {
        return $this->computermodel;
    }

    /**
     * Set the value of computermodel
     *
     * @return  self
     */
    public function setComputermodel($computermodel)
    {
        $this->computermodel = $computermodel;

        return $this;
    }

    /**
     * Get the value of computertype
     */
    public function getComputertype()
    {
        return $this->computertype;
    }

    /**
     * Set the value of computertype
     *
     * @return  self
     */
    public function setComputertype($computertype)
    {
        $this->computertype = $computertype;

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
     * Get the value of autoupdatesystem
     */
    public function getAutoupdatesystem()
    {
        return $this->autoupdatesystem;
    }

    /**
     * Set the value of autoupdatesystem
     *
     * @return  self
     */
    public function setAutoupdatesystem($autoupdatesystem)
    {
        $this->autoupdatesystem = $autoupdatesystem;

        return $this;
    }
}
