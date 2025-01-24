<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problems')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "closedate", columns: ["closedate"])]
#[ORM\Index(name: "status", columns: ["status"])]
#[ORM\Index(name: "priority", columns: ["priority"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "itilcategories_id", columns: ["itilcategories_id"])]
#[ORM\Index(name: "users_id_recipient", columns: ["users_id_recipient"])]
#[ORM\Index(name: "solvedate", columns: ["solvedate"])]
#[ORM\Index(name: "urgency", columns: ["urgency"])]
#[ORM\Index(name: "impact", columns: ["impact"])]
#[ORM\Index(name: "time_to_resolve", columns: ["time_to_resolve"])]
#[ORM\Index(name: "users_id_lastupdater", columns: ["users_id_lastupdater"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Problem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'status', type: 'integer', options: ['default' => 1])]
    private $status;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(name: 'solvedate', type: 'datetime', nullable: true)]
    private $solvedate;

    #[ORM\Column(name: 'closedate', type: 'datetime', nullable: true)]
    private $closedate;

    #[ORM\Column(name: 'time_to_resolve', type: 'datetime', nullable: true)]
    private $timeToResolve;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_recipient', referencedColumnName: 'id', nullable: true)]
    private ?User $userRecipient = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_lastupdater', referencedColumnName: 'id', nullable: true)]
    private ?User $userLastupdater = null;

    #[ORM\Column(name: 'urgency', type: 'integer', options: ['default' => 1])]
    private $urgency;

    #[ORM\Column(name: 'impact', type: 'integer', options: ['default' => 1])]
    private $impact;

    #[ORM\Column(name: 'priority', type: 'integer', options: ['default' => 1])]
    private $priority;

    #[ORM\ManyToOne(targetEntity: ItilCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ItilCategory $itilcategory = null;

    #[ORM\Column(name: 'impactcontent', type: 'text', nullable: true)]
    private $impactcontent;

    #[ORM\Column(name: 'causecontent', type: 'text', nullable: true)]
    private $causecontent;

    #[ORM\Column(name: 'symptomcontent', type: 'text', nullable: true)]
    private $symptomcontent;

    #[ORM\Column(name: 'actiontime', type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(name: 'begin_waiting_date', type: 'datetime', nullable: true)]
    private $beginWaitingDate;

    #[ORM\Column(name: 'waiting_duration', type: 'integer', options: ['default' => 0])]
    private $waitingDuration;

    #[ORM\Column(name: 'close_delay_stat', type: 'integer', options: ['default' => 0])]
    private $closeDelayStat;

    #[ORM\Column(name: 'solve_delay_stat', type: 'integer', options: ['default' => 0])]
    private $solveDelayStat;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'problem', targetEntity: ChangeProblem::class)]
    private Collection $changeProblems;

    #[ORM\OneToMany(mappedBy: 'problem', targetEntity: GroupProblem::class)]
    private Collection $groupProblems;

    #[ORM\OneToMany(mappedBy: 'problem', targetEntity: ProblemSupplier::class)]
    private Collection $problemSuppliers;

    #[ORM\OneToMany(mappedBy: 'problem', targetEntity: ProblemTicket::class)]
    private Collection $problemTickets;

    #[ORM\OneToMany(mappedBy: 'problem', targetEntity: ProblemUser::class)]
    private Collection $problemUsers;

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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }


    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }


    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }


    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSolvedate(): ?\DateTimeInterface
    {
        return $this->solvedate;
    }


    public function setSolvedate(?\DateTimeInterface $solvedate): self
    {
        $this->solvedate = $solvedate;

        return $this;
    }

    public function getClosedate(): ?\DateTimeInterface
    {
        return $this->closedate;
    }

    public function setClosedate(?\DateTimeInterface $closedate): self
    {
        $this->closedate = $closedate;

        return $this;
    }

    public function getTimeToResolve(): ?int
    {
        return $this->timeToResolve;
    }


    public function setTimeToResolve(?int $timeToResolve): self
    {
        $this->timeToResolve = $timeToResolve;

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

    public function getImpactcontent(): ?string
    {
        return $this->impactcontent;
    }


    public function setImpactcontent(?string $impactcontent): self
    {
        $this->impactcontent = $impactcontent;

        return $this;
    }

    public function getCausecontent(): ?string
    {
        return $this->causecontent;
    }


    public function setCausecontent(?string $causecontent): self
    {
        $this->causecontent = $causecontent;

        return $this;
    }

    public function getSymptomcontent(): ?string
    {
        return $this->symptomcontent;
    }


    public function setSymptomcontent(?string $symptomcontent): self
    {
        $this->symptomcontent = $symptomcontent;

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

    public function getBeginWaitingDate(): ?\DateTimeInterface
    {
        return $this->beginWaitingDate;
    }


    public function setBeginWaitingDate(?\DateTimeInterface $beginWaitingDate): self
    {
        $this->beginWaitingDate = $beginWaitingDate;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }


    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }


    /**
     * Get the value of groupProblems
     */
    public function getGroupProblems()
    {
        return $this->groupProblems;
    }

    /**
     * Set the value of groupProblems
     *
     * @return  self
     */
    public function setGroupProblems($groupProblems)
    {
        $this->groupProblems = $groupProblems;

        return $this;
    }

    /**
     * Get the value of changeProblems
     */
    public function getChangeProblems()
    {
        return $this->changeProblems;
    }

    /**
     * Set the value of changeProblems
     *
     * @return  self
     */
    public function setChangeProblems($changeProblems)
    {
        $this->changeProblems = $changeProblems;

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
     * Get the value of problemSuppliers
     */
    public function getProblemSuppliers()
    {
        return $this->problemSuppliers;
    }

    /**
     * Set the value of problemSuppliers
     *
     * @return  self
     */
    public function setProblemSuppliers($problemSuppliers)
    {
        $this->problemSuppliers = $problemSuppliers;

        return $this;
    }

    /**
     * Get the value of problemUsers
     */
    public function getProblemUsers()
    {
        return $this->problemUsers;
    }

    /**
     * Set the value of problemUsers
     *
     * @return  self
     */
    public function setProblemUsers($problemUsers)
    {
        $this->problemUsers = $problemUsers;

        return $this;
    }
}
