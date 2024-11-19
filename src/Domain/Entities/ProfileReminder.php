<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_profiles_reminders')]
#[ORM\Index(name: "reminders_id", columns: ["reminders_id"])]
#[ORM\Index(name: "profiles_id", columns: ["profiles_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class ProfileReminder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $reminders_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $profiles_id;

    #[ORM\Column(type: 'integer', options: ['default' => -1])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;

    }

    public function getRemindersId(): ?int
    {
        return $this->reminders_id;
    }


    public function setRemindersId(?int $reminders_id): self
    {
        $this->reminders_id = $reminders_id;

        return $this;
    }

    public function getProfilesId(): ?int
    {
        return $this->profiles_id;
    }


    public function setProfilesId(?int $profiles_id): self
    {
        $this->profiles_id = $profiles_id;

        return $this;
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

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }


    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

}
