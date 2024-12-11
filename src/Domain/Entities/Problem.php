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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $status;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $solvedate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $closedate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $time_to_resolve;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_recipient', referencedColumnName: 'id', nullable: true)]
    private ?User $userRecipient;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_lastupdater', referencedColumnName: 'id', nullable: true)]
    private ?User $userLastupdater;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $urgency;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $impact;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $priority;

    #[ORM\ManyToOne(targetEntity: ItilCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ItilCategory $itilcategory;

    #[ORM\Column(type: 'text', nullable: true)]
    private $impactcontent;

    #[ORM\Column(type: 'text', nullable: true)]
    private $causecontent;

    #[ORM\Column(type: 'text', nullable: true)]
    private $symptomcontent;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $begin_waiting_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $waiting_duration;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $close_delay_stat;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $solve_delay_stat;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

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
        return $this->is_recursive;
    }


    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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
        return $this->date_mod;
    }


    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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
        return $this->time_to_resolve;
    }


    public function setTimeToResolve(?int $time_to_resolve): self
    {
        $this->time_to_resolve = $time_to_resolve;

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
        return $this->begin_waiting_date;
    }


    public function setBeginWaitingDate(?\DateTimeInterface $begin_waiting_date): self
    {
        $this->begin_waiting_date = $begin_waiting_date;

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
