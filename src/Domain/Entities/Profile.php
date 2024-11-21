<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_profiles')]
#[ORM\Index(name: "interface", columns: ["interface"])]
#[ORM\Index(name: "is_default", columns: ["is_default"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "tickettemplates_id", columns: ["tickettemplates_id"])]
#[ORM\Index(name: "changetemplates_id", columns: ["changetemplates_id"])]
#[ORM\Index(name: "problemtemplates_id", columns: ["problemtemplates_id"])]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'interface', type: 'string', length: 255, nullable: true, options: ['default' => 'helpdesk'])]
    private $interface;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_default;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $helpdesk_hardware;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $helpdesk_item_type;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ['comment' => 'json encoded array of from/dest allowed status change'])]
    private $ticket_status;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ['comment' => 'json encoded array of from/dest allowed status change'])]
    private $problem_status;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $create_ticket_on_login;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickettemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changetemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $problemtemplates_id;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ['comment' => 'json encoded array of from/dest allowed status change'])]
    private $change_status;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $managed_domainrecordtypes;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInterface(): ?string
    {
        return $this->interface;
    }


    public function setInterface(?string $interface): self
    {
        $this->interface = $interface;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->is_default;
    }


    public function setIsDefault(?bool $is_default): self
    {
        $this->is_default = $is_default;

        return $this;
    }

    public function getHelpdeskHardware(): ?int
    {
        return $this->helpdesk_hardware;
    }


    public function setHelpdeskHardware(?int $helpdesk_hardware): self
    {
        $this->helpdesk_hardware = $helpdesk_hardware;

        return $this;
    }

    public function getHelpdeskItemType(): ?string
    {
        return $this->helpdesk_item_type;
    }


    public function setHelpdeskItemType(?string $helpdesk_item_type): self
    {
        $this->helpdesk_item_type = $helpdesk_item_type;

        return $this;
    }

    public function getTicketStatus(): ?string
    {
        return $this->ticket_status;
    }


    public function setTicketStatus(?string $ticket_status): self
    {
        $this->ticket_status = $ticket_status;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }


    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getProblemStatus(): ?string
    {
        return $this->problem_status;
    }


    public function setProblemStatus(?string $problem_status): self
    {
        $this->problem_status = $problem_status;

        return $this;
    }

    public function getCreateTicketOnLogin(): ?bool
    {
        return $this->create_ticket_on_login;
    }


    public function setCreateTicketOnLogin(?bool $create_ticket_on_login): self
    {
        $this->create_ticket_on_login = $create_ticket_on_login;

        return $this;
    }

    public function getTicketTemplatesId(): ?int
    {
        return $this->tickettemplates_id;
    }


    public function setTicketTemplatesId(?int $tickettemplates_id): self
    {
        $this->tickettemplates_id = $tickettemplates_id;

        return $this;
    }

    public function getChangeTemplatesId(): ?int
    {
        return $this->changetemplates_id;
    }


    public function setChangeTemplatesId(?int $changetemplates_id): self
    {
        $this->changetemplates_id = $changetemplates_id;

        return $this;
    }

    public function getProblemTemplatesId(): ?int
    {
        return $this->problemtemplates_id;
    }


    public function setProblemTemplatesId(?int $problemtemplates_id): self
    {
        $this->problemtemplates_id = $problemtemplates_id;

        return $this;
    }

    public function getChangeStatus(): ?string
    {
        return $this->change_status;
    }


    public function setChangeStatus(?string $change_status): self
    {
        $this->change_status = $change_status;

        return $this;
    }

    public function getManagedDomainRecordTypes(): ?string
    {
        return $this->managed_domainrecordtypes;
    }


    public function setManagedDomainRecordTypes(?string $managed_domainrecordtypes): self
    {
        $this->managed_domainrecordtypes = $managed_domainrecordtypes;

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
}