<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: '`name`', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'interface', type: 'string', length: 255, nullable: true, options: ['default' => 'helpdesk'])]
    private $interface = 'helpdesk';

    #[ORM\Column(name: 'is_default', type: 'boolean', options: ['default' => 0])]
    private $isDefault = 0;

    #[ORM\Column(name: 'helpdesk_hardware', type: 'integer', options: ['default' => 0])]
    private $helpdeskHardware = 0;

    #[ORM\Column(name: 'helpdesk_item_type', type: 'text', length: 65535, nullable: true)]
    private $helpdeskItemType;

    #[ORM\Column(name: 'ticket_status', type: 'text', length: 65535, nullable: true, options: ['comment' => 'json encoded array of from/dest allowed status change'])]
    private $ticketStatus;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'problem_status', type: 'text', length: 65535, nullable: true, options: ['comment' => 'json encoded array of from/dest allowed status change'])]
    private $problemStatus;

    #[ORM\Column(name: 'create_ticket_on_login', type: 'boolean', options: ['default' => 0])]
    private $createTicketOnLogin = 0;

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?TicketTemplate $tickettemplate = null;

    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?ChangeTemplate $changetemplate = null;

    #[ORM\ManyToOne(targetEntity: ProblemTemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?ProblemTemplate $problemtemplate = null;

    #[ORM\Column(name: 'change_status', type: 'text', length: 65535, nullable: true, options: ['comment' => 'json encoded array of from/dest allowed status change'])]
    private $changeStatus;

    #[ORM\Column(name: 'managed_domainrecordtypes', type: 'text', length: 65535, nullable: true)]
    private $managedDomainRecordTypes;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'profile', targetEntity: KnowbaseItemProfile::class)]
    private Collection $knowbaseitemProfiles;

    #[ORM\OneToMany(mappedBy: 'profile', targetEntity: ProfileReminder::class)]
    private Collection $profileReminders;

    #[ORM\OneToMany(mappedBy: 'profile', targetEntity: ProfileRSSFeed::class)]
    private Collection $profileRSSFeeds;

    #[ORM\OneToMany(mappedBy: 'profile', targetEntity: ProfileUser::class)]
    private Collection $profileUsers;

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
        return $this->isDefault;
    }


    public function setIsDefault(?bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getHelpdeskHardware(): ?int
    {
        return $this->helpdeskHardware;
    }


    public function setHelpdeskHardware(?int $helpdeskHardware): self
    {
        $this->helpdeskHardware = $helpdeskHardware;

        return $this;
    }

    public function getHelpdeskItemType(): ?string
    {
        return $this->helpdeskItemType;
    }


    public function setHelpdeskItemType(?string $helpdeskItemType): self
    {
        $this->helpdeskItemType = $helpdeskItemType;

        return $this;
    }

    public function getTicketStatus(): ?string
    {
        return $this->ticketStatus;
    }


    public function setTicketStatus(?string $ticketStatus): self
    {
        $this->ticketStatus = $ticketStatus;

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
        return $this->problemStatus;
    }


    public function setProblemStatus(?string $problemStatus): self
    {
        $this->problemStatus = $problemStatus;

        return $this;
    }

    public function getCreateTicketOnLogin(): ?bool
    {
        return $this->createTicketOnLogin;
    }


    public function setCreateTicketOnLogin(?bool $createTicketOnLogin): self
    {
        $this->createTicketOnLogin = $createTicketOnLogin;

        return $this;
    }

    public function getChangeStatus(): ?string
    {
        return $this->changeStatus;
    }


    public function setChangeStatus(?string $changeStatus): self
    {
        $this->changeStatus = $changeStatus;

        return $this;
    }

    public function getManagedDomainRecordTypes(): ?string
    {
        return $this->managedDomainRecordTypes;
    }


    public function setManagedDomainRecordTypes(?string $managedDomainRecordTypes): self
    {
        $this->managedDomainRecordTypes = $managedDomainRecordTypes;

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

    /**
     * Get the value of knowbaseitemProfiles
     */
    public function getKnowbaseItemProfiles()
    {
        return $this->knowbaseitemProfiles;
    }

    /**
     * Set the value of knowbaseitemProfiles
     *
     * @return  self
     */
    public function setKnowbaseItemProfiles($knowbaseitemProfiles)
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

    /**
     * Get the value of profileRSSFeeds
     */
    public function getProfileRSSFeeds()
    {
        return $this->profileRSSFeeds;
    }

    /**
     * Set the value of profileRSSFeeds
     *
     * @return  self
     */
    public function setProfileRSSFeeds($profileRSSFeeds)
    {
        $this->profileRSSFeeds = $profileRSSFeeds;

        return $this;
    }

    /**
     * Get the value of profileUsers
     */
    public function getProfileUsers()
    {
        return $this->profileUsers;
    }

    /**
     * Set the value of profileUsers
     *
     * @return  self
     */
    public function setProfileUsers($profileUsers)
    {
        $this->profileUsers = $profileUsers;

        return $this;
    }
}
