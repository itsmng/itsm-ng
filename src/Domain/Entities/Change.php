<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'date', columns: ['date'])]
#[ORM\Index(name: 'closedate', columns: ['closedate'])]
#[ORM\Index(name: 'status', columns: ['status'])]
#[ORM\Index(name: 'priority', columns: ['priority'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'itilcategories_id', columns: ['itilcategories_id'])]
#[ORM\Index(name: 'users_id_recipient', columns: ['users_id_recipient'])]
#[ORM\Index(name: 'solvedate', columns: ['solvedate'])]
#[ORM\Index(name: 'urgency', columns: ['urgency'])]
#[ORM\Index(name: 'impact', columns: ['impact'])]
#[ORM\Index(name: 'time_to_resolve', columns: ['time_to_resolve'])]
#[ORM\Index(name: 'global_validation', columns: ['global_validation'])]
#[ORM\Index(name: 'users_id_lastupdater', columns: ['users_id_lastupdater'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Change
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

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => false])]
    private $isRecursive;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => false])]
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

    #[ORM\ManyToOne(targetEntity: ITILCategory::class)]
    #[ORM\JoinColumn(name: 'itilcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?ITILCategory $itilCategory = null;

    #[ORM\Column(name: 'impactcontent', type: 'text', nullable: true)]
    private $impactcontent;

    #[ORM\Column(name: 'controlistcontent', type: 'text', nullable: true)]
    private $controlistcontent;

    #[ORM\Column(name: 'rolloutplancontent', type: 'text', nullable: true)]
    private $rolloutplancontent;

    #[ORM\Column(name: 'backoutplancontent', type: 'text', nullable: true)]
    private $backoutplancontent;

    #[ORM\Column(name: 'checklistcontent', type: 'text', nullable: true)]
    private $checklistcontent;

    #[ORM\Column(name: 'global_validation', type: 'integer', options: ['default' => 1])]
    private $globalValidation;

    #[ORM\Column(name: 'validation_percent', type: 'integer', options: ['default' => 0])]
    private $validationPercent;

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

    #[ORM\OneToMany(mappedBy: 'change', targetEntity: ChangeProblem::class)]
    private Collection $changeProblems;

    #[ORM\OneToMany(mappedBy: 'change', targetEntity: ChangeGroup::class)]
    private Collection $changeGroups;

    #[ORM\OneToMany(mappedBy: 'change', targetEntity: ChangeTicket::class)]
    private Collection $changeTickets;

    #[ORM\OneToMany(mappedBy: 'change', targetEntity: ChangeSupplier::class)]
    private Collection $changeSuppliers;

    #[ORM\OneToMany(mappedBy: 'change', targetEntity: ChangeUser::class)]
    private Collection $changeUsers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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


    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSolvedate(): ?\DateTimeInterface
    {
        return $this->solvedate;
    }

    public function setSolvedate(\DateTimeInterface $solvedate): self
    {
        $this->solvedate = $solvedate;

        return $this;
    }

    public function getClosedate(): ?\DateTimeInterface
    {
        return $this->closedate;
    }

    public function setClosedate(\DateTimeInterface $closedate): self
    {
        $this->closedate = $closedate;

        return $this;
    }

    public function getTimeToResolve(): ?\DateTimeInterface
    {
        return $this->timeToResolve;
    }

    public function setTimeToResolve(\DateTimeInterface $timeToResolve): self
    {
        $this->timeToResolve = $timeToResolve;

        return $this;
    }


    public function getUrgency(): ?int
    {
        return $this->urgency;
    }

    public function setUrgency(int $urgency): self
    {
        $this->urgency = $urgency;

        return $this;
    }

    public function getImpact(): ?int
    {
        return $this->impact;
    }

    public function setImpact(int $impact): self
    {
        $this->impact = $impact;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }


    public function getImpactcontent(): ?string
    {
        return $this->impactcontent;
    }

    public function setImpactcontent(string $impactcontent): self
    {
        $this->impactcontent = $impactcontent;

        return $this;
    }

    public function getControlistcontent(): ?string
    {
        return $this->controlistcontent;
    }

    public function setControlistcontent(string $controlistcontent): self
    {
        $this->controlistcontent = $controlistcontent;

        return $this;
    }

    public function getRolloutplancontent(): ?string
    {
        return $this->rolloutplancontent;
    }

    public function setRolloutplancontent(string $rolloutplancontent): self
    {
        $this->rolloutplancontent = $rolloutplancontent;

        return $this;
    }

    public function getBackoutplancontent(): ?string
    {
        return $this->backoutplancontent;
    }

    public function setBackoutplancontent(string $backoutplancontent): self
    {
        $this->backoutplancontent = $backoutplancontent;

        return $this;
    }

    public function getChecklistcontent(): ?string
    {
        return $this->checklistcontent;
    }

    public function setChecklistcontent(string $checklistcontent): self
    {
        $this->checklistcontent = $checklistcontent;

        return $this;
    }

    public function getGlobalValidation(): ?bool
    {
        return $this->globalValidation;
    }

    public function setGlobalValidation(bool $globalValidation): self
    {
        $this->globalValidation = $globalValidation;

        return $this;
    }

    public function getValidationPercent(): ?int
    {
        return $this->validationPercent;
    }

    public function setValidationPercent(int $validationPercent): self
    {
        $this->validationPercent = $validationPercent;

        return $this;
    }

    public function getActiontime(): ?int
    {
        return $this->actiontime;
    }

    public function setActiontime(int $actiontime): self
    {
        $this->actiontime = $actiontime;

        return $this;
    }

    public function getBeginWaitingDate(): ?\DateTimeInterface
    {
        return $this->beginWaitingDate;
    }

    public function setBeginWaitingDate(\DateTimeInterface $beginWaitingDate): self
    {
        $this->beginWaitingDate = $beginWaitingDate;

        return $this;
    }

    public function getWaitingDuration(): ?int
    {
        return $this->waitingDuration;
    }

    public function setWaitingDuration(int $waitingDuration): self
    {
        $this->waitingDuration = $waitingDuration;

        return $this;
    }

    public function getCloseDelayStat(): ?int
    {
        return $this->closeDelayStat;
    }

    public function setCloseDelayStat(int $closeDelayStat): self
    {
        $this->closeDelayStat = $closeDelayStat;

        return $this;
    }

    public function getSolveDelayStat(): ?int
    {
        return $this->solveDelayStat;
    }

    public function setSolveDelayStat(int $solveDelayStat): self
    {
        $this->solveDelayStat = $solveDelayStat;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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
     * Get the value of changeUsers
     */
    public function getChangeUsers()
    {
        return $this->changeUsers;
    }

    /**
     * Set the value of changeUsers
     *
     * @return  self
     */
    public function setChangeUsers($changeUsers)
    {
        $this->changeUsers = $changeUsers;

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
     * Get the value of changeGroups
     */
    public function getChangeGroups()
    {
        return $this->changeGroups;
    }

    /**
     * Set the value of changeGroups
     *
     * @return  self
     */
    public function setChangeGroups($changeGroups)
    {
        $this->changeGroups = $changeGroups;

        return $this;
    }

    /**
     * Get the value of changeSuppliers
     */
    public function getChangeSuppliers()
    {
        return $this->changeSuppliers;
    }

    /**
     * Set the value of changeSuppliers
     *
     * @return  self
     */
    public function setChangeSuppliers($changeSuppliers)
    {
        $this->changeSuppliers = $changeSuppliers;

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
     * Get the value of itilCategory
     */
    public function getITILCategory()
    {
        return $this->itilCategory;
    }

    /**
     * Set the value of itilCategory
     *
     * @return  self
     */
    public function setITILCategory($itilCategory)
    {
        $this->itilCategory = $itilCategory;

        return $this;
    }
}
