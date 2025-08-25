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
class RequestType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'is_helpdesk_default', type: 'boolean', options: ['default' => 0])]
    private $isHelpdeskDefault = false;

    #[ORM\Column(name: 'is_followup_default', type: 'boolean', options: ['default' => 0])]
    private $isFollowupDefault = false;

    #[ORM\Column(name: 'is_mail_default', type: 'boolean', options: ['default' => 0])]
    private $isMailDefault = false;

    #[ORM\Column(name: 'is_mailfollowup_default', type: 'boolean', options: ['default' => 0])]
    private $isMailfollowupDefault = false;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 1])]
    private $isActive = true;

    #[ORM\Column(name: 'is_ticketheader', type: 'boolean', options: ['default' => 1])]
    private $isTicketheader = true;

    #[ORM\Column(name: 'is_itilfollowup', type: 'boolean', options: ['default' => 1])]
    private $isITILfollowup = true;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

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
        return $this->isHelpdeskDefault;
    }

    public function setIsHelpdeskDefault(?string $isHelpdeskDefault): self
    {
        $this->isHelpdeskDefault = $isHelpdeskDefault;

        return $this;
    }

    public function getIsFollowupDefault(): ?string
    {
        return $this->isFollowupDefault;
    }

    public function setIsFollowupDefault(?string $isFollowupDefault): self
    {
        $this->isFollowupDefault = $isFollowupDefault;

        return $this;
    }

    public function getIsMailDefault(): ?string
    {
        return $this->isMailDefault;
    }

    public function setIsMailDefault(?string $isMailDefault): self
    {
        $this->isMailDefault = $isMailDefault;

        return $this;
    }

    public function getIsMailfollowupDefault(): ?string
    {
        return $this->isMailfollowupDefault;
    }

    public function setIsMailfollowupDefault(?string $isMailfollowupDefault): self
    {
        $this->isMailfollowupDefault = $isMailfollowupDefault;

        return $this;
    }

    public function getIsActive(): ?string
    {
        return $this->isActive;
    }

    public function setIsActive(?string $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsTicketheader(): ?string
    {
        return $this->isTicketheader;
    }

    public function setIsTicketheader(?string $isTicketheader): self
    {
        $this->isTicketheader = $isTicketheader;

        return $this;
    }

    public function getIsITILfollowup(): ?string
    {
        return $this->isITILfollowup;
    }

    public function setIsITILfollowup(?string $isITILfollowup): self
    {
        $this->isITILfollowup = $isITILfollowup;

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
        return $this->dateMod;
    }

    public function setDateMod(?string $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?string $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

}
