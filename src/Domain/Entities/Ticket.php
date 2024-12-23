<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Itsmng\Domain\Entities\Requesttype as EntitiesRequesttype;
use OlaLevel;
use RequestType;
use SlaLevel;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickets")]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "closedate", columns: ["closedate"])]
#[ORM\Index(name: "status", columns: ["status"])]
#[ORM\Index(name: "priority", columns: ["priority"])]
#[ORM\Index(name: "request_type", columns: ["requesttypes_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "users_id_recipient", columns: ["users_id_recipient"])]
#[ORM\Index(name: "solvedate", columns: ["solvedate"])]
#[ORM\Index(name: "urgency", columns: ["urgency"])]
#[ORM\Index(name: "impact", columns: ["impact"])]
#[ORM\Index(name: "global_validation", columns: ["global_validation"])]
#[ORM\Index(name: "slas_id_tto", columns: ["slas_id_tto"])]
#[ORM\Index(name: "slas_id_ttr", columns: ["slas_id_ttr"])]
#[ORM\Index(name: "time_to_resolve", columns: ["time_to_resolve"])]
#[ORM\Index(name: "time_to_own", columns: ["time_to_own"])]
#[ORM\Index(name: "olas_id_tto", columns: ["olas_id_tto"])]
#[ORM\Index(name: "olas_id_ttr", columns: ["olas_id_ttr"])]
#[ORM\Index(name: "slalevels_id_ttr", columns: ["slalevels_id_ttr"])]
#[ORM\Index(name: "internal_time_to_resolve", columns: ["internal_time_to_resolve"])]
#[ORM\Index(name: "internal_time_to_own", columns: ["internal_time_to_own"])]
#[ORM\Index(name: "users_id_lastupdater", columns: ["users_id_lastupdater"])]
#[ORM\Index(name: "type", columns: ["type"])]
#[ORM\Index(name: "itilcategories_id", columns: ["itilcategories_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "locations_id", columns: ["locations_id"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "ola_waiting_duration", columns: ["ola_waiting_duration"])]
#[ORM\Index(name: "olalevels_id_ttr", columns: ["olalevels_id_ttr"])]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $closedate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $solvedate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_lastupdater', referencedColumnName: 'id', nullable: true)]
    private ?User $userLastupdater;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $status;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_recipient', referencedColumnName: 'id', nullable: true)]
    private ?User $userRecipient;

    #[ORM\ManyToOne(targetEntity: EntitiesRequesttype::class)]
    #[ORM\JoinColumn(name: 'requesttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?EntitiesRequesttype $requesttype;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $urgency;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $impact;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $priority;

    #[ORM\ManyToOne(targetEntity: ItilCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ItilCategory $itilcategory;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $global_validation;

    #[ORM\ManyToOne(targetEntity: Sla::class)]
    #[ORM\JoinColumn(name: 'slas_id_ttr', referencedColumnName: 'id', nullable: true)]
    private ?Sla $slaTtr;

    #[ORM\ManyToOne(targetEntity: Sla::class)]
    #[ORM\JoinColumn(name: 'slas_id_tto', referencedColumnName: 'id', nullable: true)]
    private ?Sla $slaTto;

    #[ORM\ManyToOne(targetEntity: SlaLevel::class)]
    #[ORM\JoinColumn(name: 'slalevels_id_ttr', referencedColumnName: 'id', nullable: true)]
    private ?SlaLevel $slaLevelTtr;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $time_to_resolve;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $time_to_own;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin_waiting_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $sla_waiting_duration;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $ola_waiting_duration;

    #[ORM\ManyToOne(targetEntity: Ola::class)]
    #[ORM\JoinColumn(name: 'olas_id_tto', referencedColumnName: 'id', nullable: true)]
    private ?Ola $olaTto;

    #[ORM\ManyToOne(targetEntity: Ola::class)]
    #[ORM\JoinColumn(name: 'olas_id_ttr', referencedColumnName: 'id', nullable: true)]
    private ?Ola $olaTtr;

    #[ORM\ManyToOne(targetEntity: OlaLevel::class)]
    #[ORM\JoinColumn(name: 'olalevels_id_ttr', referencedColumnName: 'id', nullable: true)]
    private ?OlaLevel $olaLevelTtr;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $ola_ttr_begin_date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $internal_time_to_resolve;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $internal_time_to_own;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $waiting_duration;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $close_delay_stat;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $solve_delay_stat;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $takeintoaccount_delay_stat;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $validation_percent;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: ChangeTicket::class)]
    private Collection $changeTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: GroupTicket::class)]
    private Collection $groupTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: OlalevelTicket::class)]
    private Collection $olalevelTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: ProblemTicket::class)]
    private Collection $problemTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: ProjecttaskTicket::class)]
    private Collection $projecttaskTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: SlalevelTicket::class)]
    private Collection $slalevelTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: SupplierTicket::class)]
    private Collection $supplierTickets;

    #[ORM\OneToMany(mappedBy: 'ticket1', targetEntity: TicketTicket::class)]
    private Collection $ticketTickets1;

    #[ORM\OneToMany(mappedBy: 'ticket2', targetEntity: TicketTicket::class)]
    private Collection $ticketTickets2;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: TicketUser::class)]
    private Collection $ticketUsers;


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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getClosedate(): ?\DateTime
    {
        return $this->closedate;
    }

    public function setClosedate(?\DateTime $closedate): self
    {
        $this->closedate = $closedate;

        return $this;
    }

    public function getSolvedate(): ?\DateTime
    {
        return $this->solvedate;
    }

    public function setSolvedate(?\DateTime $solvedate): self
    {
        $this->solvedate = $solvedate;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUrgency(): ?int
    {
        return $this->urgency;
    }

    public function setUrgency(?int $urgency): self
    {
        $this->urgency = $urgency;

        return $this;
    }

    public function getImpact(): ?int
    {
        return $this->impact;
    }

    public function setImpact(?int $impact): self
    {
        $this->impact = $impact;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getGlobalValidation(): ?int
    {
        return $this->global_validation;
    }

    public function setGlobalValidation(?int $global_validation): self
    {
        $this->global_validation = $global_validation;

        return $this;
    }


    public function getTimeToResolve(): ?\DateTime
    {
        return $this->time_to_resolve;
    }

    public function setTimeToResolve(?\DateTime $time_to_resolve): self
    {
        $this->time_to_resolve = $time_to_resolve;

        return $this;
    }

    public function getTimeToOwn(): ?\DateTime
    {
        return $this->time_to_own;
    }

    public function setTimeToOwn(?\DateTime $time_to_own): self
    {
        $this->time_to_own = $time_to_own;

        return $this;
    }

    public function getBeginWaitingDate(): ?\DateTime
    {
        return $this->begin_waiting_date;
    }

    public function setBeginWaitingDate(?\DateTime $begin_waiting_date): self
    {
        $this->begin_waiting_date = $begin_waiting_date;

        return $this;
    }

    public function getSlaWaitingDuration(): ?int
    {
        return $this->sla_waiting_duration;
    }

    public function setSlaWaitingDuration(?int $sla_waiting_duration): self
    {
        $this->sla_waiting_duration = $sla_waiting_duration;

        return $this;
    }

    public function getOlaWaitingDuration(): ?int
    {
        return $this->ola_waiting_duration;
    }

    public function setOlaWaitingDuration(?int $ola_waiting_duration): self
    {
        $this->ola_waiting_duration = $ola_waiting_duration;

        return $this;
    }

    public function getOlaTtrBeginDate(): ?\DateTime
    {
        return $this->ola_ttr_begin_date;
    }

    public function setOlaTtrBeginDate(?\DateTime $ola_ttr_begin_date): self
    {
        $this->ola_ttr_begin_date = $ola_ttr_begin_date;

        return $this;
    }

    public function getInternalTimeToResolve(): ?\DateTime
    {
        return $this->internal_time_to_resolve;
    }

    public function setInternalTimeToResolve(?\DateTime $internal_time_to_resolve): self
    {
        $this->internal_time_to_resolve = $internal_time_to_resolve;

        return $this;
    }

    public function getInternalTimeToOwn(): ?\DateTime
    {
        return $this->internal_time_to_own;
    }

    public function setInternalTimeToOwn(?\DateTime $internal_time_to_own): self
    {
        $this->internal_time_to_own = $internal_time_to_own;

        return $this;
    }

    public function getWaitingDuration(): ?int
    {
        return $this->waiting_duration;
    }

    public function setWaitingDuration(?int $waiting_duration): self
    {
        $this->waiting_duration = $waiting_duration;

        return $this;
    }

    public function getCloseDelayStat(): ?int
    {
        return $this->close_delay_stat;
    }

    public function setCloseDelayStat(?int $close_delay_stat): self
    {
        $this->close_delay_stat = $close_delay_stat;

        return $this;
    }

    public function getSolveDelayStat(): ?int
    {
        return $this->solve_delay_stat;
    }

    public function setSolveDelayStat(?int $solve_delay_stat): self
    {
        $this->solve_delay_stat = $solve_delay_stat;

        return $this;
    }

    public function getTakeintoaccountDelayStat(): ?int
    {
        return $this->takeintoaccount_delay_stat;
    }

    public function setTakeintoaccountDelayStat(?int $takeintoaccount_delay_stat): self
    {
        $this->takeintoaccount_delay_stat = $takeintoaccount_delay_stat;

        return $this;
    }

    public function getActiontime(): ?int
    {
        return $this->actiontime;
    }

    public function setActiontime(?int $actiontime): self
    {
        $this->actiontime = $actiontime;

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

    public function getValidationPercent(): ?int
    {
        return $this->validation_percent;
    }

    public function setValidationPercent(?int $validation_percent): self
    {
        $this->validation_percent = $validation_percent;

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
     * Get the value of groupTickets
     */
    public function getGroupTickets()
    {
        return $this->groupTickets;
    }

    /**
     * Set the value of groupTickets
     *
     * @return  self
     */
    public function setGroupTickets($groupTickets)
    {
        $this->groupTickets = $groupTickets;

        return $this;
    }

    /**
     * Get the value of changeTickets
     */
    public function getChangeTickets()
    {
        return $this->changeTickets;
    }

    /**
     * Set the value of changeTickets
     *
     * @return  self
     */
    public function setChangeTickets($changeTickets)
    {
        $this->changeTickets = $changeTickets;

        return $this;
    }

    /**
     * Get the value of olalevelTickets
     */
    public function getOlalevelTickets()
    {
        return $this->olalevelTickets;
    }

    /**
     * Set the value of olalevelTickets
     *
     * @return  self
     */
    public function setOlalevelTickets($olalevelTickets)
    {
        $this->olalevelTickets = $olalevelTickets;

        return $this;
    }

    /**
     * Get the value of problemTickets
     */
    public function getProblemTickets()
    {
        return $this->problemTickets;
    }

    /**
     * Set the value of problemTickets
     *
     * @return  self
     */
    public function setProblemTickets($problemTickets)
    {
        $this->problemTickets = $problemTickets;

        return $this;
    }

    /**
     * Get the value of projecttaskTickets
     */
    public function getProjecttaskTickets()
    {
        return $this->projecttaskTickets;
    }

    /**
     * Set the value of projecttaskTickets
     *
     * @return  self
     */
    public function setProjecttaskTickets($projecttaskTickets)
    {
        $this->projecttaskTickets = $projecttaskTickets;

        return $this;
    }

    /**
     * Get the value of slalevelTickets
     */
    public function getSlalevelTickets()
    {
        return $this->slalevelTickets;
    }

    /**
     * Set the value of slalevelTickets
     *
     * @return  self
     */
    public function setSlalevelTickets($slalevelTickets)
    {
        $this->slalevelTickets = $slalevelTickets;

        return $this;
    }

    /**
     * Get the value of supplierTickets
     */
    public function getSupplierTickets()
    {
        return $this->supplierTickets;
    }

    /**
     * Set the value of supplierTickets
     *
     * @return  self
     */
    public function setSupplierTickets($supplierTickets)
    {
        $this->supplierTickets = $supplierTickets;

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
     * Get the value of userLastupdater
     */
    public function getUserLastupdater()
    {
        return $this->userLastupdater;
    }

    /**
     * Set the value of userLastupdater
     *
     * @return  self
     */
    public function setUserLastupdater($userLastupdater)
    {
        $this->userLastupdater = $userLastupdater;

        return $this;
    }

    /**
     * Get the value of userRecipient
     */
    public function getUserRecipient()
    {
        return $this->userRecipient;
    }

    /**
     * Set the value of userRecipient
     *
     * @return  self
     */
    public function setUserRecipient($userRecipient)
    {
        $this->userRecipient = $userRecipient;

        return $this;
    }

    /**
     * Get the value of requesttype
     */
    public function getRequesttype()
    {
        return $this->requesttype;
    }

    /**
     * Set the value of requesttype
     *
     * @return  self
     */
    public function setRequesttype($requesttype)
    {
        $this->requesttype = $requesttype;

        return $this;
    }

    /**
     * Get the value of itilcategory
     */
    public function getItilcategory()
    {
        return $this->itilcategory;
    }

    /**
     * Set the value of itilcategory
     *
     * @return  self
     */
    public function setItilcategory($itilcategory)
    {
        $this->itilcategory = $itilcategory;

        return $this;
    }

    /**
     * Get the value of slaTtr
     */
    public function getSlaTtr()
    {
        return $this->slaTtr;
    }

    /**
     * Set the value of slaTtr
     *
     * @return  self
     */
    public function setSlaTtr($slaTtr)
    {
        $this->slaTtr = $slaTtr;

        return $this;
    }

    /**
     * Get the value of slaTto
     */
    public function getSlaTto()
    {
        return $this->slaTto;
    }

    /**
     * Set the value of slaTto
     *
     * @return  self
     */
    public function setSlaTto($slaTto)
    {
        $this->slaTto = $slaTto;

        return $this;
    }

    /**
     * Get the value of slaLevelTtr
     */
    public function getSlaLevelTtr()
    {
        return $this->slaLevelTtr;
    }

    /**
     * Set the value of slaLevelTtr
     *
     * @return  self
     */
    public function setSlaLevelTtr($slaLevelTtr)
    {
        $this->slaLevelTtr = $slaLevelTtr;

        return $this;
    }

    /**
     * Get the value of olaTto
     */
    public function getOlaTto()
    {
        return $this->olaTto;
    }

    /**
     * Set the value of olaTto
     *
     * @return  self
     */
    public function setOlaTto($olaTto)
    {
        $this->olaTto = $olaTto;

        return $this;
    }

    /**
     * Get the value of olaTtr
     */
    public function getOlaTtr()
    {
        return $this->olaTtr;
    }

    /**
     * Set the value of olaTtr
     *
     * @return  self
     */
    public function setOlaTtr($olaTtr)
    {
        $this->olaTtr = $olaTtr;

        return $this;
    }

    /**
     * Get the value of olaLevelTtr
     */
    public function getOlaLevelTtr()
    {
        return $this->olaLevelTtr;
    }

    /**
     * Set the value of olaLevelTtr
     *
     * @return  self
     */
    public function setOlaLevelTtr($olaLevelTtr)
    {
        $this->olaLevelTtr = $olaLevelTtr;

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
     * Get the value of ticketTickets1
     */
    public function getTicketTickets1()
    {
        return $this->ticketTickets1;
    }

    /**
     * Set the value of ticketTickets1
     *
     * @return  self
     */
    public function setTicketTickets1($ticketTickets1)
    {
        $this->ticketTickets1 = $ticketTickets1;

        return $this;
    }

    /**
     * Get the value of ticketTickets2
     */
    public function getTicketTickets2()
    {
        return $this->ticketTickets2;
    }

    /**
     * Set the value of ticketTickets2
     *
     * @return  self
     */
    public function setTicketTickets2($ticketTickets2)
    {
        $this->ticketTickets2 = $ticketTickets2;

        return $this;
    }

    /**
     * Get the value of ticketUsers
     */
    public function getTicketUsers()
    {
        return $this->ticketUsers;
    }

    /**
     * Set the value of ticketUsers
     *
     * @return  self
     */
    public function setTicketUsers($ticketUsers)
    {
        $this->ticketUsers = $ticketUsers;

        return $this;
    }
}
