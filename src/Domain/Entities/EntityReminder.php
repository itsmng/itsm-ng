<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

//Table: glpi_entities_reminders
//
//Select data Show structure Alter table New item
//Column	Type	Comment
//id	int(11) Auto Increment
//reminders_id	int(11) [0]
//entities_id	int(11) [0]
//is_recursive	tinyint(1) [0]
//Indexes
//PRIMARY	id
//INDEX	reminders_id
//INDEX	entities_id
//INDEX	is_recursive

#[ORM\Entity]
#[ORM\Table(name: "glpi_entities_reminders")]
#[ORM\Index(columns: ["reminders_id"])]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["is_recursive"])]
class EntityReminder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $reminders_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemindersId(): ?int
    {
        return $this->reminders_id;
    }

    public function setRemindersId(int $reminders_id): self
    {
        $this->reminders_id = $reminders_id;

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
}
