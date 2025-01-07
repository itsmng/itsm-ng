<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Location;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_certificates')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_template', columns: ['is_template'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'certificatetypes_id', columns: ['certificatetypes_id'])]
#[ORM\Index(name: 'users_id_tech', columns: ['users_id_tech'])]
#[ORM\Index(name: 'groups_id_tech', columns: ['groups_id_tech'])]
#[ORM\Index(name: 'groups_id', columns: ['groups_id'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'locations_id', columns: ['locations_id'])]
#[ORM\Index(name: 'manufacturers_id', columns: ['manufacturers_id'])]
#[ORM\Index(name: 'states_id', columns: ['states_id'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
class Certificate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;


    #[ORM\ManyToOne(targetEntity: CertificateType::class)]
    #[ORM\JoinColumn(name: 'certificatetypes_id', referencedColumnName: 'id', nullable: true)]
    private ?CertificateType $certificateType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $dns_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $dns_suffix;


    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id_tech', referencedColumnName: 'id', nullable: true, options:['comment' => 'RELATION to glpi_groups (id)'])]
    private ?Group $techGroup;


    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'RELATION to glpi_locations (id)'])]
    private ?Location $location;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'RELATION to glpi_manufacturers (id)'])]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact_num;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_autosign;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date_expiration;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'RELATION to states (id)'])]
    private ?State $state;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $command;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $certificate_request;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $certificate_item;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

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

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsTemplate(): ?bool
    {
        return $this->is_template;
    }

    public function setIsTemplate(bool $is_template): self
    {
        $this->is_template = $is_template;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->template_name;
    }

    public function setTemplateName(string $template_name): self
    {
        $this->template_name = $template_name;

        return $this;
    }

    public function getDnsName(): ?string
    {
        return $this->dns_name;
    }

    public function setDnsName(string $dns_name): self
    {
        $this->dns_name = $dns_name;

        return $this;
    }

    public function getDnsSuffix(): ?string
    {
        return $this->dns_suffix;
    }

    public function setDnsSuffix(string $dns_suffix): self
    {
        $this->dns_suffix = $dns_suffix;

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
        return $this->contact_num;
    }

    public function setContactNum(string $contact_num): self
    {
        $this->contact_num = $contact_num;

        return $this;
    }

    public function getIsAutosign(): ?bool
    {
        return $this->is_autosign;
    }

    public function setIsAutosign(bool $is_autosign): self
    {
        $this->is_autosign = $is_autosign;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->date_expiration;
    }

    public function setDateExpiration(\DateTimeInterface $date_expiration): self
    {
        $this->date_expiration = $date_expiration;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getCertificateRequest(): ?string
    {
        return $this->certificate_request;
    }

    public function setCertificateRequest(string $certificate_request): self
    {
        $this->certificate_request = $certificate_request;

        return $this;
    }

    public function getCertificateItem(): ?string
    {
        return $this->certificate_item;
    }

    public function setCertificateItem(string $certificate_item): self
    {
        $this->certificate_item = $certificate_item;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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
     * Get the value of certificateType
     */
    public function getCertificateType()
    {
        return $this->certificateType;
    }

    /**
     * Set the value of certificateType
     *
     * @return  self
     */
    public function setCertificateType($certificateType)
    {
        $this->certificateType = $certificateType;

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
