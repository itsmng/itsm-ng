<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_contracts')]
#[ORM\Index(name: 'begin_date', columns: ['begin_date'])]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'contracttypes_id', columns: ['contracttypes_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'use_monday', columns: ['use_monday'])]
#[ORM\Index(name: 'use_saturday', columns: ['use_saturday'])]
#[ORM\Index(name: 'alert', columns: ['alert'])]
#[ORM\Index(name: 'states_id', columns: ['states_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive = 0;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'num', type: 'string', length: 255, nullable: true)]
    private $num;

    #[ORM\ManyToOne(targetEntity: ContractType::class)]
    #[ORM\JoinColumn(name: 'contracttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?ContractType $contractType = null;

    #[ORM\Column(name: 'begin_date', type: 'date', nullable: true)]
    private $beginDate;

    #[ORM\Column(name: 'duration', type: 'integer', options: ['default' => 0])]
    private $duration = 0;

    #[ORM\Column(name: 'notice', type: 'integer', options: ['default' => 0])]
    private $notice = 0;

    #[ORM\Column(name: 'periodicity', type: 'integer', options: ['default' => 0])]
    private $periodicity = 0;

    #[ORM\Column(name: 'billing', type: 'integer', options: ['default' => 0])]
    private $billing = 0;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'accounting_number', type: 'string', length: 255, nullable: true)]
    private $accountingNumber;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'week_begin_hour', type: 'time', options: ['default' => '00:00:00'])]
    private $weekBeginHour = null;

    #[ORM\Column(name: 'week_end_hour', type: 'time', options: ['default' => '00:00:00'])]
    private $weekEndHour = null;

    #[ORM\Column(name: 'saturday_begin_hour', type: 'time', options: ['default' => '00:00:00'])]
    private $saturdayBeginHour = null;

    #[ORM\Column(name: 'saturday_end_hour', type: 'time', options: ['default' => '00:00:00'])]
    private $saturdayEndHour = null;

    #[ORM\Column(name: 'use_saturday', type: 'boolean', options: ['default' => 0])]
    private $useSaturday = 0;

    #[ORM\Column(name: 'monday_begin_hour', type: 'time', options: ['default' => '00:00:00'])]
    private $mondayBeginHour = null;

    #[ORM\Column(name: 'monday_end_hour', type: 'time', options: ['default' => '00:00:00'])]
    private $mondayEndHour = null;

    #[ORM\Column(name: 'use_monday', type: 'boolean', options: ['default' => 0])]
    private $useMonday = 0;

    #[ORM\Column(name: 'max_links_allowed', type: 'integer', options: ['default' => 0])]
    private $maxLinksAllowed = 0;

    #[ORM\Column(name: 'alert', type: 'integer', options: ['default' => 0])]
    private $alert = 0;

    #[ORM\Column(name: 'renewal', type: 'integer', options: ['default' => 0])]
    private $renewal = 0;

    #[ORM\Column(name: 'template_name', type: 'string', length: 255, nullable: true)]
    private $templateName;

    #[ORM\Column(name: 'is_template', type: 'boolean', options: ['default' => 0])]
    private $isTemplate = 0;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: ContractSupplier::class)]
    private Collection $contractSuppliers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?int
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(int $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function getNum(): ?string
    {
        return $this->num;
    }

    public function setNum(?string $num): self
    {
        $this->num = $num;

        return $this;
    }


    public function getBeginDate(): ?\DateTimeInterface
    {
        return $this->beginDate;
    }

    public function setBeginDate(\DateTimeInterface|string|null $beginDate): self
    {
        if (is_string($beginDate)) {
            $beginDate = new DateTime($beginDate);
        }
        $this->beginDate = $beginDate;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getNotice(): ?int
    {
        return $this->notice;
    }

    public function setNotice(int $notice): self
    {
        $this->notice = $notice;

        return $this;
    }

    public function getPeriodicity(): ?int
    {
        return $this->periodicity;
    }

    public function setPeriodicity(int $periodicity): self
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    public function getBilling(): ?int
    {
        return $this->billing;
    }

    public function setBilling(int $billing): self
    {
        $this->billing = $billing;

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

    public function getAccountingNumber(): ?string
    {
        return $this->accountingNumber;
    }

    public function setAccountingNumber(?string $accountingNumber): self
    {
        $this->accountingNumber = $accountingNumber;

        return $this;
    }

    public function getIsDeleted(): ?int
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(int $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getWeekBeginHour(): ?\DateTimeInterface
    {
        if ($this->weekBeginHour === null) {
            $this->weekBeginHour = new DateTime('00:00:00');
        }
        return $this->weekBeginHour;
    }

    public function setWeekBeginHour(\DateTimeInterface|string|null $weekBeginHour): self
    {
        if (is_string($weekBeginHour)) {
            $weekBeginHour = new DateTime($weekBeginHour);
        }
        $this->weekBeginHour = $weekBeginHour;

        return $this;
    }

    public function getWeekEndHour(): ?\DateTimeInterface
    {
        if ($this->weekEndHour === null) {
            $this->weekEndHour = new DateTime('00:00:00');
        }
        return $this->weekEndHour;
    }

    public function setWeekEndHour(\DateTimeInterface|string|null $weekEndHour): self
    {
        if (is_string($weekEndHour)) {
            $weekEndHour = new DateTime($weekEndHour);
        }
        $this->weekEndHour = $weekEndHour;

        return $this;
    }

    public function getSaturdayBeginHour(): ?\DateTimeInterface
    {
        if ($this->saturdayBeginHour === null) {
            $this->saturdayBeginHour = new DateTime('00:00:00');
        }
        return $this->saturdayBeginHour;
    }

    public function setSaturdayBeginHour(\DateTimeInterface|string|null $saturdayBeginHour): self
    {
        if (is_string($saturdayBeginHour)) {
            $saturdayBeginHour = new DateTime($saturdayBeginHour);
        }
        $this->saturdayBeginHour = $saturdayBeginHour;

        return $this;
    }

    public function getSaturdayEndHour(): ?\DateTimeInterface
    {
        if ($this->saturdayEndHour === null) {
            $this->saturdayEndHour = new DateTime('00:00:00');
        }
        return $this->saturdayEndHour;
    }

    public function setSaturdayEndHour(\DateTimeInterface|string|null $saturdayEndHour): self
    {
        if (is_string($saturdayEndHour)) {
            $saturdayEndHour = new DateTime($saturdayEndHour);
        }
        $this->saturdayEndHour = $saturdayEndHour;

        return $this;
    }

    public function getUseSaturday(): ?int
    {
        return $this->useSaturday;
    }

    public function setUseSaturday(int $useSaturday): self
    {
        $this->useSaturday = $useSaturday;

        return $this;
    }

    public function getMondayBeginHour(): ?\DateTimeInterface
    {
        if ($this->mondayBeginHour === null) {
            $this->mondayBeginHour = new DateTime('00:00:00');
        }
        return $this->mondayBeginHour;
    }

    public function setMondayBeginHour(\DateTimeInterface|string|null $mondayBeginHour): self
    {
        if (is_string($mondayBeginHour)) {
            $mondayBeginHour = new DateTime($mondayBeginHour);
        }
        $this->mondayBeginHour = $mondayBeginHour;

        return $this;
    }

    public function getMondayEndHour(): ?\DateTimeInterface
    {
        if ($this->mondayEndHour === null) {
            $this->mondayEndHour = new DateTime('00:00:00');
        }
        return $this->mondayEndHour;
    }

    public function setMondayEndHour(\DateTimeInterface|string|null $mondayEndHour): self
    {
        if (is_string($mondayEndHour)) {
            $mondayEndHour = new DateTime($mondayEndHour);
        }
        $this->mondayEndHour = $mondayEndHour;

        return $this;
    }

    public function getUseMonday(): ?int
    {
        return $this->useMonday;
    }

    public function setUseMonday(int $useMonday): self
    {
        $this->useMonday = $useMonday;

        return $this;
    }

    public function getMaxLinksAllowed(): ?int
    {
        return $this->maxLinksAllowed;
    }

    public function setMaxLinksAllowed(int |string|null $maxLinksAllowed): self
    {
        $this->maxLinksAllowed = (int) $maxLinksAllowed;

        return $this;
    }

    public function getAlert(): ?int
    {
        return $this->alert;
    }

    public function setAlert(int $alert): self
    {
        $this->alert = $alert;

        return $this;
    }

    public function getRenewal(): ?int
    {
        return $this->renewal;
    }

    public function setRenewal(int $renewal): self
    {
        $this->renewal = $renewal;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function getIsTemplate(): ?int
    {
        return $this->isTemplate;
    }

    public function setIsTemplate(int $isTemplate): self
    {
        $this->isTemplate = $isTemplate;

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
     * Get the value of contracttype
     */
    public function getContractType()
    {
        return $this->contractType;
    }

    /**
     * Set the value of contracttype
     *
     * @return  self
     */
    public function setContractType($contractType)
    {
        $this->contractType = $contractType;

        return $this;
    }

    /**
     * Get the value of state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the value of state
     *
     * @return  self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the value of contractSuppliers
     */
    public function getContractSuppliers()
    {
        return $this->contractSuppliers;
    }

    /**
     * Set the value of contractSuppliers
     *
     * @return  self
     */
    public function setContractSuppliers($contractSuppliers)
    {
        $this->contractSuppliers = $contractSuppliers;

        return $this;
    }
}
