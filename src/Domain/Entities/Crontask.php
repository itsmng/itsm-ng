<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_crontasks')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['itemtype', 'name'])]
#[ORM\Index(name: 'mode', columns: ['mode'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]

class Crontask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'string', length: 150, options: ['comment' => 'task name'])]
    private $name;

    #[ORM\Column(type: 'integer', options: ['comment' => 'second between launch'])]
    private $frequency;

    #[ORM\Column(type: 'integer', nullable: true, options: ['comment' => 'task specify parameter'])]
    private $param;

    #[ORM\Column(type: 'integer', options: ['default' => 1, 'comment' => '0:disabled, 1:waiting, 2:running'])]
    private $state;

    #[ORM\Column(type: 'integer', options: ['default' => 1, 'comment' => '1:internal, 2:external'])]
    private $mode;

    #[ORM\Column(type: 'integer', options: ['default' => 3, 'comment' => '1:internal, 2:external, 3:both'])]
    private $allowmode;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $hourmin;

    #[ORM\Column(type: 'integer', options: ['default' => 24])]
    private $hourmax;

    #[ORM\Column(type: 'integer', options: ['default' => 30, 'comment' => 'number of days'])]
    private $logs_lifetime;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['default' => 'CURRENT_TIMESTAMP', 'comment' => 'last run date'])]
    private $lastrun;

    #[ORM\Column(type: 'integer', nullable: true, options: ['comment' => 'last run return code'])]
    private $lastcode;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

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

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getParam(): ?int
    {
        return $this->param;
    }

    public function setParam(int $param): self
    {
        $this->param = $param;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getAllowmode(): ?int
    {
        return $this->allowmode;
    }

    public function setAllowmode(int $allowmode): self
    {
        $this->allowmode = $allowmode;

        return $this;
    }

    public function getHourmin(): ?int
    {
        return $this->hourmin;
    }

    public function setHourmin(int $hourmin): self
    {
        $this->hourmin = $hourmin;

        return $this;
    }

    public function getHourmax(): ?int
    {
        return $this->hourmax;
    }

    public function setHourmax(int $hourmax): self
    {
        $this->hourmax = $hourmax;

        return $this;
    }

    public function getLogsLifetime(): ?int
    {
        return $this->logs_lifetime;
    }

    public function setLogsLifetime(int $logs_lifetime): self
    {
        $this->logs_lifetime = $logs_lifetime;

        return $this;
    }

    public function getLastrun(): ?\DateTimeInterface
    {
        return $this->lastrun;
    }

    public function setLastrun(\DateTimeInterface $lastrun): self
    {
        $this->lastrun = $lastrun;

        return $this;
    }

    public function getLastcode(): ?int
    {
        return $this->lastcode;
    }

    public function setLastcode(int $lastcode): self
    {
        $this->lastcode = $lastcode;

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
}
