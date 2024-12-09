<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $num;

    #[ORM\ManyToOne(targetEntity: Contracttype::class)]
    #[ORM\JoinColumn(name: 'contracttypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Contracttype $contracttype;

    #[ORM\Column(type: 'date', nullable: true)]
    private $begin_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $duration;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $notice;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $periodicity;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $billing;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $accounting_number;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'time', options: ['default' => '00:00:00'])]
    private $week_begin_hour;

    #[ORM\Column(type: 'time', options: ['default' => '00:00:00'])]
    private $week_end_hour;

    #[ORM\Column(type: 'time', options: ['default' => '00:00:00'])]
    private $saturday_begin_hour;

    #[ORM\Column(type: 'time', options: ['default' => '00:00:00'])]
    private $saturday_end_hour;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $use_saturday;

    #[ORM\Column(type: 'time', options: ['default' => '00:00:00'])]
    private $monday_begin_hour;

    #[ORM\Column(type: 'time', options: ['default' => '00:00:00'])]
    private $monday_end_hour;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $use_monday;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $max_links_allowed;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $alert;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $renewal;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $template_name;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_template;

    #[ORM\ManyToOne(targetEntity: State::class)]
    #[ORM\JoinColumn(name: 'states_id', referencedColumnName: 'id', nullable: true)]
    private ?State $state;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: ContractSupplier::class)]
    private Collection $contractSuppliers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?int
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(int $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

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
        return $this->begin_date;
    }

    public function setBeginDate(\DateTimeInterface $begin_date): self
    {
        $this->begin_date = $begin_date;

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
        return $this->accounting_number;
    }

    public function setAccountingNumber(?string $accounting_number): self
    {
        $this->accounting_number = $accounting_number;

        return $this;
    }

    public function getIsDeleted(): ?int
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(int $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getWeekBeginHour(): ?\DateTimeInterface
    {
        return $this->week_begin_hour;
    }

    public function setWeekBeginHour(\DateTimeInterface $week_begin_hour): self
    {
        $this->week_begin_hour = $week_begin_hour;

        return $this;
    }

    public function getWeekEndHour(): ?\DateTimeInterface
    {
        return $this->week_end_hour;
    }

    public function setWeekEndHour(\DateTimeInterface $week_end_hour): self
    {
        $this->week_end_hour = $week_end_hour;

        return $this;
    }

    public function getSaturdayBeginHour(): ?\DateTimeInterface
    {
        return $this->saturday_begin_hour;
    }

    public function setSaturdayBeginHour(\DateTimeInterface $saturday_begin_hour): self
    {
        $this->saturday_begin_hour = $saturday_begin_hour;

        return $this;
    }

    public function getSaturdayEndHour(): ?\DateTimeInterface
    {
        return $this->saturday_end_hour;
    }

    public function setSaturdayEndHour(\DateTimeInterface $saturday_end_hour): self
    {
        $this->saturday_end_hour = $saturday_end_hour;

        return $this;
    }

    public function getUseSaturday(): ?int
    {
        return $this->use_saturday;
    }

    public function setUseSaturday(int $use_saturday): self
    {
        $this->use_saturday = $use_saturday;

        return $this;
    }

    public function getMondayBeginHour(): ?\DateTimeInterface
    {
        return $this->monday_begin_hour;
    }

    public function setMondayBeginHour(\DateTimeInterface $monday_begin_hour): self
    {
        $this->monday_begin_hour = $monday_begin_hour;

        return $this;
    }

    public function getMondayEndHour(): ?\DateTimeInterface
    {
        return $this->monday_end_hour;
    }

    public function setMondayEndHour(\DateTimeInterface $monday_end_hour): self
    {
        $this->monday_end_hour = $monday_end_hour;

        return $this;
    }

    public function getUseMonday(): ?int
    {
        return $this->use_monday;
    }

    public function setUseMonday(int $use_monday): self
    {
        $this->use_monday = $use_monday;

        return $this;
    }

    public function getMaxLinksAllowed(): ?int
    {
        return $this->max_links_allowed;
    }

    public function setMaxLinksAllowed(int $max_links_allowed): self
    {
        $this->max_links_allowed = $max_links_allowed;

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
        return $this->template_name;
    }

    public function setTemplateName(?string $template_name): self
    {
        $this->template_name = $template_name;

        return $this;
    }

    public function getIsTemplate(): ?int
    {
        return $this->is_template;
    }

    public function setIsTemplate(int $is_template): self
    {
        $this->is_template = $is_template;

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
     * Get the value of contracttype
     */
    public function getContracttype()
    {
        return $this->contracttype;
    }

    /**
     * Set the value of contracttype
     *
     * @return  self
     */
    public function setContracttype($contracttype)
    {
        $this->contracttype = $contracttype;

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
