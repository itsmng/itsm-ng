<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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
#[ORM\Index(name: "users_id_tech", columns: ['users_id_tech'])]
#[ORM\Index(name: "monitortypes_id", columns: ['monitortypes_id'])]
#[ORM\Index(name: "is_deleted", columns: ['is_deleted'])]
#[ORM\Index(name: "groups_id_tech", columns: ['groups_id_tech'])]
#[ORM\Index(name: "is_dynamic", columns: ['is_dynamic'])]
#[ORM\Index(name: "serial", columns: ['serial'])]
#[ORM\Index(name: "otherserial", columns: ['otherserial'])]
#[ORM\Index(name: "date_creation", columns: ['date_creation'])]
#[ORM\Index(name: "is_recursive", columns: ['is_recursive'])]
class Monitor
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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_tech;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id_tech;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, options: ['default' => "0.00"])]
    private $size;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_micro;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_speaker;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_subd;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_bnc;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_dvi;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_pivot;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_hdmi;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $have_displayport;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $monitortypes_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $monitormodels_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $manufacturers_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_global;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $states_id;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, options: ['default' => "0.0000"], nullable: true)]
    private $ticket_tco;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_dynamic;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

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
        return $this->have_micro;
    }

    public function setHaveMicro(bool $have_micro): self
    {
        $this->have_micro = $have_micro;

        return $this;
    }

    public function getHaveSpeaker(): ?bool
    {
        return $this->have_speaker;
    }

    public function setHaveSpeaker(bool $have_speaker): self
    {
        $this->have_speaker = $have_speaker;

        return $this;
    }

    public function getHaveSubd(): ?bool
    {
        return $this->have_subd;
    }

    public function setHaveSubd(bool $have_subd): self
    {
        $this->have_subd = $have_subd;

        return $this;
    }

    public function getHaveBnc(): ?bool
    {
        return $this->have_bnc;
    }

    public function setHaveBnc(bool $have_bnc): self
    {
        $this->have_bnc = $have_bnc;

        return $this;
    }

    public function getHaveDvi(): ?bool
    {
        return $this->have_dvi;
    }

    public function setHaveDvi(bool $have_dvi): self
    {
        $this->have_dvi = $have_dvi;

        return $this;
    }

    public function getHavePivot(): ?bool
    {
        return $this->have_pivot;
    }

    public function setHavePivot(bool $have_pivot): self
    {
        $this->have_pivot = $have_pivot;

        return $this;
    }

    public function getHaveHdmi(): ?bool
    {
        return $this->have_hdmi;
    }

    public function setHaveHdmi(bool $have_hdmi): self
    {
        $this->have_hdmi = $have_hdmi;

        return $this;
    }

    public function getHaveDisplayport(): ?bool
    {
        return $this->have_displayport;
    }

    public function setHaveDisplayport(bool $have_displayport): self
    {
        $this->have_displayport = $have_displayport;

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

    public function getMonitortypesId(): ?int
    {
        return $this->monitortypes_id;
    }

    public function setMonitortypesId(int $monitortypes_id): self
    {
        $this->monitortypes_id = $monitortypes_id;

        return $this;
    }

    public function getMonitormodelsId(): ?int
    {
        return $this->monitormodels_id;
    }

    public function setMonitormodelsId(int $monitormodels_id): self
    {
        $this->monitormodels_id = $monitormodels_id;

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

    public function getIsGlobal(): ?bool
    {
        return $this->is_global;
    }

    public function setIsGlobal(bool $is_global): self
    {
        $this->is_global = $is_global;

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

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

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

    public function setStatesId(int $statess_id): self
    {
        $this->states_id = $statess_id;

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

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
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

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }
}
