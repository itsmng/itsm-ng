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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', name: 'entities_id', options: ['default' => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_recipient;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_lastupdater;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $urgency;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $impact;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $priority;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $itilcategories_id;

    #[ORM\Column(type: 'text', nullable: true)]
    private $impactcontent;

    #[ORM\Column(type: 'text', nullable: true)]
    private $controlistcontent;

    #[ORM\Column(type: 'text', nullable: true)]
    private $rolloutplancontent;

    #[ORM\Column(type: 'text', nullable: true)]
    private $backoutplancontent;

    #[ORM\Column(type: 'text', nullable: true)]
    private $checklistcontent;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $global_validation;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $validation_percent;

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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

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

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

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
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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
        return $this->time_to_resolve;
    }

    public function setTimeToResolve(\DateTimeInterface $time_to_resolve): self
    {
        $this->time_to_resolve = $time_to_resolve;

        return $this;
    }

    public function getUsersIdRecipient(): ?int
    {
        return $this->users_id_recipient;
    }

    public function setUsersIdRecipient(int $users_id_recipient): self
    {
        $this->users_id_recipient = $users_id_recipient;

        return $this;
    }

    public function getUsersIdLastupdater(): ?int
    {
        return $this->users_id_lastupdater;
    }

    public function setUsersIdLastupdater(int $users_id_lastupdater): self
    {
        $this->users_id_lastupdater = $users_id_lastupdater;

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

    public function getItilcategoriesId(): ?int
    {
        return $this->itilcategories_id;
    }

    public function setItilcategoriesId(int $itilcategories_id): self
    {
        $this->itilcategories_id = $itilcategories_id;

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
        return $this->global_validation;
    }

    public function setGlobalValidation(bool $global_validation): self
    {
        $this->global_validation = $global_validation;

        return $this;
    }

    public function getValidationPercent(): ?int
    {
        return $this->validation_percent;
    }

    public function setValidationPercent(int $validation_percent): self
    {
        $this->validation_percent = $validation_percent;

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
        return $this->begin_waiting_date;
    }

    public function setBeginWaitingDate(\DateTimeInterface $begin_waiting_date): self
    {
        $this->begin_waiting_date = $begin_waiting_date;

        return $this;
    }

    public function getWaitingDuration(): ?int
    {
        return $this->waiting_duration;
    }

    public function setWaitingDuration(int $waiting_duration): self
    {
        $this->waiting_duration = $waiting_duration;

        return $this;
    }

    public function getCloseDelayStat(): ?int
    {
        return $this->close_delay_stat;
    }

    public function setCloseDelayStat(int $close_delay_stat): self
    {
        $this->close_delay_stat = $close_delay_stat;

        return $this;
    }

    public function getSolveDelayStat(): ?int
    {
        return $this->solve_delay_stat;
    }

    public function setSolveDelayStat(int $solve_delay_stat): self
    {
        $this->solve_delay_stat = $solve_delay_stat;

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
}
