<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "glpi_tickets")]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "closedate", columns: ["closedate"])]
#[ORM\Index(name: "status", columns: ["status"])]
#[ORM\Index(name: "priority", columns: ["priority"])]
#[ORM\Index(name: "request_type", columns: ["requesttypes_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "recipient_users_id", columns: ["recipient_users_id"])]
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
#[ORM\Index(name: "lastupdater_users_id", columns: ["lastupdater_users_id"])]
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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;


    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date = null;

    #[ORM\Column(name: 'closedate', type: 'datetime', nullable: true)]
    private $closedate = null;

    #[ORM\Column(name: 'solvedate', type: 'datetime', nullable: true)]
    private $solvedate = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'lastupdater_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $lastupdaterUser = null;

    #[ORM\Column(name: 'status', type: 'integer', options: ['default' => 1])]
    private $status = 1;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'recipient_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $recipientUser = null;

    #[ORM\ManyToOne(targetEntity: RequestType::class)]
    #[ORM\JoinColumn(name: 'requesttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?RequestType $requesttype = null;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content = null;

    #[ORM\Column(name: 'urgency', type: 'integer', options: ['default' => 1])]
    private $urgency = 1;

    #[ORM\Column(name: 'impact', type: 'integer', options: ['default' => 1])]
    private $impact = 1;

    #[ORM\Column(name: 'priority', type: 'integer', options: ['default' => 1])]
    private $priority = 1;

    #[ORM\ManyToOne(targetEntity: ITILCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ITILCategory $itilcategory = null;


    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 1])]
    private $type = 1;

    #[ORM\Column(name: 'global_validation', type: 'integer', options: ['default' => 1])]
    private $globalValidation = 1;

    #[ORM\Column(name: 'slas_id_ttr', type: 'integer', options: ['default' => 0])]
    private $slasIdTtr = 0;

    #[ORM\Column(name: 'slas_id_tto', type: 'integer', options: ['default' => 0])]
    private $slasIdTto = 0;

    #[ORM\Column(name: 'slalevels_id_ttr', type: 'integer', options: ['default' => 0])]
    private $slalevelsIdTtr = 0;

    #[ORM\Column(name: 'time_to_resolve', type: 'datetime', nullable: true)]
    private $timeToResolve = null;

    #[ORM\Column(name: 'time_to_own', type: 'datetime', nullable: true)]
    private $timeToOwn = null;

    #[ORM\Column(name: 'begin_waiting_date', type: 'datetime', nullable: true)]
    private $beginWaitingDate = null;

    #[ORM\Column(name: 'sla_waiting_duration', type: 'integer', options: ['default' => 0])]
    private $slaWaitingDuration = 0;

    #[ORM\Column(name: 'ola_waiting_duration', type: 'integer', options: ['default' => 0])]
    private $olaWaitingDuration = 0;

    #[ORM\Column(name: 'olas_id_tto', type: 'integer', options: ['default' => 0])]
    private $olasIdTto = 0;

    #[ORM\Column(name: 'olas_id_ttr', type: 'integer', options: ['default' => 0])]
    private $olasIdTtr = 0;

    #[ORM\Column(name: 'olalevels_id_ttr', type: 'integer', options: ['default' => 0])]
    private $olalevelsIdTtr = 0;

    #[ORM\Column(name: 'ola_ttr_begin_date', type: 'datetime', nullable: true)]
    private $olaTtrBeginDate = null;

    #[ORM\Column(name: 'internal_time_to_resolve', type: 'datetime', nullable: true)]
    private $internalTimeToResolve = null;

    #[ORM\Column(name: 'internal_time_to_own', type: 'datetime', nullable: true)]
    private $internalTimeToOwn = null;

    #[ORM\Column(name: 'waiting_duration', type: 'integer', options: ['default' => 0])]
    private $waitingDuration = 0;

    #[ORM\Column(name: 'close_delay_stat', type: 'integer', options: ['default' => 0])]
    private $closeDelayStat = 0;

    #[ORM\Column(name: 'solve_delay_stat', type: 'integer', options: ['default' => 0])]
    private $solveDelayStat = 0;

    #[ORM\Column(name: 'takeintoaccount_delay_stat', type: 'integer', options: ['default' => 0])]
    private $takeintoaccountDelayStat = 0;

    #[ORM\Column(name: 'actiontime', type: 'integer', options: ['default' => 0])]
    private $actiontime = 0;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\Column(name: 'validation_percent', type: 'integer', options: ['default' => 0])]
    private $validationPercent = 0;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: ChangeTicket::class)]
    private Collection $changeTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: GroupTicket::class)]
    private Collection $groupTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: OlaLevelTicket::class)]
    private Collection $olalevelTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: ProblemTicket::class)]
    private Collection $problemTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: ProjectTaskTicket::class)]
    private Collection $projecttaskTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: SlaLevelTicket::class)]
    private Collection $slalevelTickets;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: SupplierTicket::class)]
    private Collection $supplierTickets;

    public function __construct()
    {
        $this->changeTickets = new ArrayCollection();
        $this->groupTickets = new ArrayCollection();
        $this->olalevelTickets = new ArrayCollection();
        $this->problemTickets = new ArrayCollection();
        $this->projecttaskTickets = new ArrayCollection();
        $this->slalevelTickets = new ArrayCollection();
        $this->supplierTickets = new ArrayCollection();
    }

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface|string|null $date): self
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        $this->date = $date;

        return $this;
    }

    public function getClosedate(): ?\DateTimeInterface
    {
        return $this->closedate;
    }

    public function setClosedate(\DateTimeInterface|string|null $closedate): self
    {
        if (is_string($closedate)) {
            $closedate = new DateTime($closedate);
        }
        $this->closedate = $closedate;

        return $this;
    }

    public function getSolvedate(): ?\DateTimeInterface
    {
        return $this->solvedate;
    }

    public function setSolvedate(\DateTimeInterface|string|null $solvedate): self
    {
        if (is_string($solvedate)) {
            $solvedate = new DateTime($solvedate);
        }
        $this->solvedate = $solvedate;

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
        return $this->globalValidation;
    }

    public function setGlobalValidation(?int $globalValidation): self
    {
        $this->globalValidation = $globalValidation;

        return $this;
    }

    public function getSlasIdTtr(): ?int
    {
        return $this->slasIdTtr;
    }

    public function setSlasIdTtr(?int $slasIdTtr): self
    {
        $this->slasIdTtr = $slasIdTtr;

        return $this;
    }

    public function getSlasIdTto(): ?int
    {
        return $this->slasIdTto;
    }

    public function setSlasIdTto(?int $slasIdTto): self
    {
        $this->slasIdTto = $slasIdTto;

        return $this;
    }

    public function getSlaLevelsIdTtr(): ?int
    {
        return $this->slalevelsIdTtr;
    }

    public function setSlaLevelsIdTtr(?int $slalevelsIdTtr): self
    {
        $this->slalevelsIdTtr = $slalevelsIdTtr;

        return $this;
    }

    public function getTimeToResolve(): ?\DateTimeInterface
    {
        return $this->timeToResolve;
    }

    public function setTimeToResolve(\DateTimeInterface|string|null $timeToResolve): self
    {
        if (is_string($timeToResolve)) {
            $timeToResolve = new DateTime($timeToResolve);
        }
        $this->timeToResolve = $timeToResolve;

        return $this;
    }

    public function getTimeToOwn(): ?\DateTimeInterface
    {
        return $this->timeToOwn;
    }

    public function setTimeToOwn(\DateTimeInterface|string|null $timeToOwn): self
    {
        if (is_string($timeToOwn)) {
            $timeToOwn = new DateTime($timeToOwn);
        }
        $this->timeToOwn = $timeToOwn;

        return $this;
    }

    public function getBeginWaitingDate(): ?\DateTimeInterface
    {
        return $this->beginWaitingDate;
    }

    public function setBeginWaitingDate(\DateTimeInterface|string|null $beginWaitingDate): self
    {
        if (is_string($beginWaitingDate)) {
            $beginWaitingDate = new DateTime($beginWaitingDate);
        }
        $this->beginWaitingDate = $beginWaitingDate;

        return $this;
    }

    public function getSlaWaitingDuration(): ?int
    {
        return $this->slaWaitingDuration;
    }

    public function setSlaWaitingDuration(?int $slaWaitingDuration): self
    {
        $this->slaWaitingDuration = $slaWaitingDuration;

        return $this;
    }

    public function getOlaWaitingDuration(): ?int
    {
        return $this->olaWaitingDuration;
    }

    public function setOlaWaitingDuration(?int $olaWaitingDuration): self
    {
        $this->olaWaitingDuration = $olaWaitingDuration;

        return $this;
    }

    public function getOlasIdTto(): ?int
    {
        return $this->olasIdTto;
    }

    public function setOlasIdTto(?int $olasIdTto): self
    {
        $this->olasIdTto = $olasIdTto;

        return $this;
    }

    public function getOlasIdTtr(): ?int
    {
        return $this->olasIdTtr;
    }

    public function setOlasIdTtr(?int $olasIdTtr): self
    {
        $this->olasIdTtr = $olasIdTtr;

        return $this;
    }

    public function getOlaLevelsIdTtr(): ?int
    {
        return $this->olalevelsIdTtr;
    }

    public function setOlaLevelsIdTtr(?int $olalevelsIdTtr): self
    {
        $this->olalevelsIdTtr = $olalevelsIdTtr;

        return $this;
    }

    public function getOlaTtrBeginDate(): ?\DateTimeInterface
    {
        return $this->olaTtrBeginDate;
    }

    public function setOlaTtrBeginDate(\DateTimeInterface|string|null $olaTtrBeginDate): self
    {
        if (is_string($olaTtrBeginDate)) {
            $olaTtrBeginDate = new DateTime($olaTtrBeginDate);
        }
        $this->olaTtrBeginDate = $olaTtrBeginDate;

        return $this;
    }

    public function getInternalTimeToResolve(): ?\DateTimeInterface
    {
        return $this->internalTimeToResolve;
    }

    public function setInternalTimeToResolve(\DateTimeInterface|string|null $internalTimeToResolve): self
    {
        if (is_string($internalTimeToResolve)) {
            $internalTimeToResolve = new DateTime($internalTimeToResolve);
        }
        $this->internalTimeToResolve = $internalTimeToResolve;

        return $this;
    }

    public function getInternalTimeToOwn(): ?\DateTimeInterface
    {
        return $this->internalTimeToOwn;
    }

    public function setInternalTimeToOwn(\DateTimeInterface|string|null $internalTimeToOwn): self
    {
        if (is_string($internalTimeToOwn)) {
            $internalTimeToOwn = new DateTime($internalTimeToOwn);
        }
        $this->internalTimeToOwn = $internalTimeToOwn;

        return $this;
    }

    public function getWaitingDuration(): ?int
    {
        return $this->waitingDuration;
    }

    public function setWaitingDuration(?int $waitingDuration): self
    {
        $this->waitingDuration = $waitingDuration;

        return $this;
    }

    public function getCloseDelayStat(): ?int
    {
        return $this->closeDelayStat;
    }

    public function setCloseDelayStat(?int $closeDelayStat): self
    {
        $this->closeDelayStat = $closeDelayStat;

        return $this;
    }

    public function getSolveDelayStat(): ?int
    {
        return $this->solveDelayStat;
    }

    public function setSolveDelayStat(?int $solveDelayStat): self
    {
        $this->solveDelayStat = $solveDelayStat;

        return $this;
    }

    public function getTakeintoaccountDelayStat(): ?int
    {
        return $this->takeintoaccountDelayStat;
    }

    public function setTakeintoaccountDelayStat(?int $takeintoaccountDelayStat): self
    {
        $this->takeintoaccountDelayStat = $takeintoaccountDelayStat;

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
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }


    public function getValidationPercent(): ?int
    {
        return $this->validationPercent;
    }

    public function setValidationPercent(?int $validationPercent): self
    {
        $this->validationPercent = $validationPercent;

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
    public function getOlaLevelTickets()
    {
        return $this->olalevelTickets;
    }

    /**
     * Set the value of olalevelTickets
     *
     * @return  self
     */
    public function setOlaLevelTickets($olalevelTickets)
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
    public function getProjectTaskTickets()
    {
        return $this->projecttaskTickets;
    }

    /**
     * Set the value of projecttaskTickets
     *
     * @return  self
     */
    public function setProjectTaskTickets($projecttaskTickets)
    {
        $this->projecttaskTickets = $projecttaskTickets;

        return $this;
    }

    /**
     * Get the value of slalevelTickets
     */
    public function getSlaLevelTickets()
    {
        return $this->slalevelTickets;
    }

    /**
     * Set the value of slalevelTickets
     *
     * @return  self
     */
    public function setSlaLevelTickets($slalevelTickets)
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
     * Get the value of requesttype
     */
    public function getRequestType()
    {
        return $this->requesttype;
    }

    /**
     * Set the value of requesttype
     *
     * @return  self
     */
    public function setRequestType($requesttype)
    {
        $this->requesttype = $requesttype;

        return $this;
    }

    /**
     * Get the value of itilcategory
     */
    public function getITILCategory()
    {
        return $this->itilcategory;
    }

    /**
     * Set the value of itilcategory
     *
     * @return  self
     */
    public function setITILcategory($itilcategory)
    {
        $this->itilcategory = $itilcategory;

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
     * Get the value of lastupdaterUser
     */
    public function getLastupdaterUser()
    {
        return $this->lastupdaterUser;
    }

    /**
     * Set the value of lastupdaterUser
     *
     * @return  self
     */
    public function setLastupdaterUser($lastupdaterUser)
    {
        $this->lastupdaterUser = $lastupdaterUser;

        return $this;
    }

    /**
     * Get the value of recipientUser
     */
    public function getRecipientUser()
    {
        return $this->recipientUser;
    }

    /**
     * Set the value of recipientUser
     *
     * @return  self
     */
    public function setRecipientUser($recipientUser)
    {
        $this->recipientUser = $recipientUser;

        return $this;
    }
}
