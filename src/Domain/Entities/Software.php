<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_softwares")]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_template", columns: ["is_template"])]
#[ORM\Index(name: "is_update", columns: ["is_update"])]
#[ORM\Index(name: "softwarecategories_id", columns: ["softwarecategories_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "manufacturers_id", columns: ["manufacturers_id"])]
#[ORM\Index(name: "groups_id", columns: ["groups_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "softwares_id", columns: ["softwares_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_helpdesk_visible", columns: ["is_helpdesk_visible"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Software
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_update;

    #[ORM\ManyToOne(targetEntity: Software::class)]
    #[ORM\JoinColumn(name: 'softwares_id', referencedColumnName: 'id', nullable: true)]
    private ?Software $software;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[ORM\JoinColumn(name: 'manufacturers_id', referencedColumnName: 'id', nullable: true)]
    private ?Manufacturer $manufacturer;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $group;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, nullable: true, options: ['default' => "0.0000"])]
    private $ticket_tco;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_helpdesk_visible;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwarecategories_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_valid;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsUpdate(): ?bool
    {
        return $this->is_update;
    }

    public function setIsUpdate(?bool $is_update): self
    {
        $this->is_update = $is_update;

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

    public function getDateMod(): ?\DateTime
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTime $date_mod): self
    {
        $this->date_mod = $date_mod;

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

    public function getIsHelpdeskVisible(): ?bool
    {
        return $this->is_helpdesk_visible;
    }

    public function setIsHelpdeskVisible(?bool $is_helpdesk_visible): self
    {
        $this->is_helpdesk_visible = $is_helpdesk_visible;

        return $this;
    }

    public function getSoftwarecategoriesId(): ?int
    {
        return $this->softwarecategories_id;
    }

    public function setSoftwarecategoriesId(?int $softwarecategories_id): self
    {
        $this->softwarecategories_id = $softwarecategories_id;

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
