<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

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

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?TicketTemplate $tickettemplate;

    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?ChangeTemplate $changetemplate;

    #[ORM\ManyToOne(targetEntity: Problemtemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Problemtemplate $problemtemplate;

    #[ORM\Column(type: 'text', length: 65535, nullable: true, options: ['comment' => 'json encoded array of from/dest allowed status change'])]
    private $change_status;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $managed_domainrecordtypes;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'profile', targetEntity: KnowbaseitemProfile::class)]
    private Collection $knowbaseitemProfiles;

    #[ORM\OneToMany(mappedBy: 'profile', targetEntity: ProfileReminder::class)]
    private Collection $profileReminders;

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

    /**
     * Get the value of knowbaseitemProfiles
     */
    public function getKnowbaseitemProfiles()
    {
        return $this->knowbaseitemProfiles;
    }

    /**
     * Set the value of knowbaseitemProfiles
     *
     * @return  self
     */
    public function setKnowbaseitemProfiles($knowbaseitemProfiles)
    {
        $this->knowbaseitemProfiles = $knowbaseitemProfiles;

        return $this;
    }

    /**
     * Get the value of tickettemplate
     */
    public function getTickettemplate()
    {
        return $this->tickettemplate;
    }

    /**
     * Set the value of tickettemplate
     *
     * @return  self
     */
    public function setTickettemplate($tickettemplate)
    {
        $this->tickettemplate = $tickettemplate;

        return $this;
    }

    /**
     * Get the value of changetemplate
     */
    public function getChangetemplate()
    {
        return $this->changetemplate;
    }

    /**
     * Set the value of changetemplate
     *
     * @return  self
     */
    public function setChangetemplate($changetemplate)
    {
        $this->changetemplate = $changetemplate;

        return $this;
    }

    /**
     * Get the value of problemtemplate
     */
    public function getProblemtemplate()
    {
        return $this->problemtemplate;
    }

    /**
     * Set the value of problemtemplate
     *
     * @return  self
     */
    public function setProblemtemplate($problemtemplate)
    {
        $this->problemtemplate = $problemtemplate;

        return $this;
    }

    /**
     * Get the value of profileReminders
     */ 
    public function getProfileReminders()
    {
        return $this->profileReminders;
    }

    /**
     * Set the value of profileReminders
     *
     * @return  self
     */ 
    public function setProfileReminders($profileReminders)
    {
        $this->profileReminders = $profileReminders;

        return $this;
    }
}
