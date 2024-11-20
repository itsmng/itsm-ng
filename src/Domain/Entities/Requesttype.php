<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_requesttypes')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_helpdesk_default", columns: ["is_helpdesk_default"])]
#[ORM\Index(name: "is_followup_default", columns: ["is_followup_default"])]
#[ORM\Index(name: "is_mail_default", columns: ["is_mail_default"])]
#[ORM\Index(name: "is_mailfollowup_default", columns: ["is_mailfollowup_default"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "is_ticketheader", columns: ["is_ticketheader"])]
#[ORM\Index(name: "is_itilfollowup", columns: ["is_itilfollowup"])]
class Requesttype
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_helpdesk_default;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_followup_default;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_mail_default;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_mailfollowup_default;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_active;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_ticketheader;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_itilfollowup;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

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

    public function getIsHelpdeskDefault(): ?string
    {
        return $this->is_helpdesk_default;
    }

    public function setIsHelpdeskDefault(?string $is_helpdesk_default): self
    {
        $this->is_helpdesk_default = $is_helpdesk_default;

        return $this;
    }

    public function getIsFollowupDefault(): ?string
    {
        return $this->is_followup_default;
    }

    public function setIsFollowupDefault(?string $is_followup_default): self
    {
        $this->is_followup_default = $is_followup_default;

        return $this;
    }

    public function getIsMailDefault(): ?string
    {
        return $this->is_mail_default;
    }

    public function setIsMailDefault(?string $is_mail_default): self
    {
        $this->is_mail_default = $is_mail_default;

        return $this;
    }

    public function getIsMailfollowupDefault(): ?string
    {
        return $this->is_mailfollowup_default;
    }

    public function setIsMailfollowupDefault(?string $is_mailfollowup_default): self
    {
        $this->is_mailfollowup_default = $is_mailfollowup_default;

        return $this;
    }

    public function getIsActive(): ?string
    {
        return $this->is_active;
    }

    public function setIsActive(?string $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getIsTicketheader(): ?string
    {
        return $this->is_ticketheader;
    }

    public function setIsTicketheader(?string $is_ticketheader): self
    {
        $this->is_ticketheader = $is_ticketheader;

        return $this;
    }

    public function getIsItilfollowup(): ?string
    {
        return $this->is_itilfollowup;
    }

    public function setIsItilfollowup(?string $is_itilfollowup): self
    {
        $this->is_itilfollowup = $is_itilfollowup;

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

    public function getDateMod(): ?string
    {
        return $this->date_mod;
    }

    public function setDateMod(?string $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->date_creation;
    }

    public function setDateCreation(?string $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

}