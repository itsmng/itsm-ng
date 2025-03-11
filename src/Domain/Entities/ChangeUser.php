<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changes_users')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['changes_id', 'type', 'users_id', 'alternative_email'])]
#[ORM\Index(name: 'user', columns: ['users_id', 'type'])]

class ChangeUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Change::class, inversedBy: 'changeUsers')]
    #[ORM\JoinColumn(name: 'changes_id', referencedColumnName: 'id', nullable: true)]
    private ?Change $change = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'changeUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 1])]
    private $type = 1;

    #[ORM\Column(name: 'use_notification', type: 'boolean', options: ['default' => false])]
    private $useNotification = false;

    #[ORM\Column(name: 'alternative_email', type: 'string', length: 255, nullable: true)]
    private $alternativeEmail;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUseNotification(): ?bool
    {
        return $this->useNotification;
    }

    public function setUseNotification(bool $useNotification): self
    {
        $this->useNotification = $useNotification;

        return $this;
    }

    public function getAlternativeEmail(): ?string
    {
        return $this->alternativeEmail;
    }

    public function setAlternativeEmail(?string $alternativeEmail): self
    {
        $this->alternativeEmail = $alternativeEmail;

        return $this;
    }

    /**
     * Get the value of change
     */
    public function getChange()
    {
        return $this->change;
    }

    /**
     * Set the value of change
     *
     * @return  self
     */
    public function setChange($change)
    {
        $this->change = $change;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
