<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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
#[ORM\Index(name: 'users_id_tech', columns: ['users_id_tech'])]
#[ORM\Index(name: 'computertypes_id', columns: ['computertypes_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'groups_id_tech', columns: ['groups_id_tech'])]
#[ORM\Index(name: 'is_dynamic', columns: ['is_dynamic'])]
#[ORM\Index(name: 'serial', columns: ['serial'])]
#[ORM\Index(name: 'otherserial', columns: ['otherserial'])]
#[ORM\Index(name: 'uuid', columns: ['uuid'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
class Computer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact_num;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_tech;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id_tech;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $autoupdatesystems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $networks_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $computermodels_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $computertypes_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_dynamic;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $states_id;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => 0.0], nullable: true)]
    private $ticket_tco;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

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
        return $this->contact_num;
    }

    public function setContactNum(string $contact_num): self
    {
        $this->contact_num = $contact_num;

        return $this;
    }

    public function getUsersIdTech(): ?int
    {
        return $this->users_id_tech;
    }

    public function setUsersIdTech(int $users_id_tech): self
    {
        $this->users_id_tech = $users_id_tech;

        return $this;
    }

    public function getGroupsIdTech(): ?int
    {
        return $this->groups_id_tech;
    }

    public function setGroupsIdTech(int $groups_id_tech): self
    {
        $this->groups_id_tech = $groups_id_tech;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getAutoupdatesystemsId(): ?int
    {
        return $this->autoupdatesystems_id;
    }

    public function setAutoupdatesystemsId(int $autoupdatesystems_id): self
    {
        $this->autoupdatesystems_id = $autoupdatesystems_id;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getNetworksId(): ?int
    {
        return $this->networks_id;
    }

    public function setNetworksId(int $networks_id): self
    {
        $this->networks_id = $networks_id;

        return $this;
    }

    public function getComputermodelsId(): ?int
    {
        return $this->computermodels_id;
    }

    public function setComputermodelsId(int $computermodels_id): self
    {
        $this->computermodels_id = $computermodels_id;

        return $this;
    }

    public function getComputertypesId(): ?int
    {
        return $this->computertypes_id;
    }

    public function setComputertypesId(int $computertypes_id): self
    {
        $this->computertypes_id = $computertypes_id;

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

    public function getManufacturersId(): ?int
    {
        return $this->manufacturers_id;
    }

    public function setManufacturersId(int $manufacturers_id): self
    {
        $this->manufacturers_id = $manufacturers_id;

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

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $user_id): self
    {
        $this->users_id = $user_id;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
    }

    public function getStatesId(): ?int
    {
        return $this->states_id;
    }

    public function setStatesId(int $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }

    public function getTicketTco(): ?float
    {
        return $this->ticket_tco;
    }

    public function setTicketTco(float $ticket_tco): self
    {
        $this->ticket_tco = $ticket_tco;

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

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }
}
