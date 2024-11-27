<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_lastupdater;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $status;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_recipient;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $requesttypes_id;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $urgency;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $impact;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $priority;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $itilcategories_id;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $global_validation;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $slas_id_ttr;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $slas_id_tto;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $slalevels_id_ttr;

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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $olas_id_tto;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $olas_id_ttr;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $olalevels_id_ttr;

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

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $validation_percent;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
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

    public function getUsersIdLastupdater(): ?int
    {
        return $this->users_id_lastupdater;
    }

    public function setUsersIdLastupdater(?int $users_id_lastupdater): self
    {
        $this->users_id_lastupdater = $users_id_lastupdater;

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

    public function getUsersIdRecipient(): ?int
    {
        return $this->users_id_recipient;
    }

    public function setUsersIdRecipient(?int $users_id_recipient): self
    {
        $this->users_id_recipient = $users_id_recipient;

        return $this;
    }

    public function getRequesttypesId(): ?int
    {
        return $this->requesttypes_id;
    }

    public function setRequesttypesId(?int $requesttypes_id): self
    {
        $this->requesttypes_id = $requesttypes_id;

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

    public function getItilcategoriesId(): ?int
    {
        return $this->itilcategories_id;
    }

    public function setItilcategoriesId(?int $itilcategories_id): self
    {
        $this->itilcategories_id = $itilcategories_id;

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

    public function getSlasIdTtr(): ?int
    {
        return $this->slas_id_ttr;
    }

    public function setSlasIdTtr(?int $slas_id_ttr): self
    {
        $this->slas_id_ttr = $slas_id_ttr;

        return $this;
    }

    public function getSlasIdTto(): ?int
    {
        return $this->slas_id_tto;
    }

    public function setSlasIdTto(?int $slas_id_tto): self
    {
        $this->slas_id_tto = $slas_id_tto;

        return $this;
    }

    public function getSlalevelsIdTtr(): ?int
    {
        return $this->slalevels_id_ttr;
    }

    public function setSlalevelsIdTtr(?int $slalevels_id_ttr): self
    {
        $this->slalevels_id_ttr = $slalevels_id_ttr;

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

    public function getOlasIdTto(): ?int
    {
        return $this->olas_id_tto;
    }

    public function setOlasIdTto(?int $olas_id_tto): self
    {
        $this->olas_id_tto = $olas_id_tto;

        return $this;
    }

    public function getOlasIdTtr(): ?int
    {
        return $this->olas_id_ttr;
    }

    public function setOlasIdTtr(?int $olas_id_ttr): self
    {
        $this->olas_id_ttr = $olas_id_ttr;

        return $this;
    }

    public function getOlalevelsIdTtr(): ?int
    {
        return $this->olalevels_id_ttr;
    }

    public function setOlalevelsIdTtr(?int $olalevels_id_ttr): self
    {
        $this->olalevels_id_ttr = $olalevels_id_ttr;

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

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(?int $locations_id): self
    {
        $this->locations_id = $locations_id;

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

}
