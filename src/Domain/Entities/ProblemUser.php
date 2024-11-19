<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problems_users')]
#[ORM\UniqueConstraint(columns: ["problems_id", "type", "users_id", "alternative_email"])]
#[ORM\Index(name: "users_id_type", columns: ["users_id", "type"])]
class ProblemUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $problems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $type;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $use_notification;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $alternative_email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProblemsId(): ?int
    {
        return $this->problems_id;
    }


    public function setProblemsId(?int $problems_id): self
    {
        $this->problems_id = $problems_id;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }


    public function setUsersId(?int $users_id): self
    {
        $this->users_id = $users_id;

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

    public function getUseNotification(): ?bool
    {
        return $this->use_notification;
    }


    public function setUseNotification(?bool $use_notification): self
    {
        $this->use_notification = $use_notification;

        return $this;
    }

    public function getAlternativeEmail(): ?string
    {
        return $this->alternative_email;
    }


    public function setAlternativeEmail(?string $alternative_email): self
    {
        $this->alternative_email = $alternative_email;

        return $this;
    }

}
