<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_entities_reminders")]
#[ORM\Index(name: "reminders_id", columns: ["reminders_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class EntityReminder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $reminders_id;

    #[ORM\ManyToOne(targetEntity: Reminder::class, inversedBy: 'entityReminders')]
    #[ORM\JoinColumn(name: 'reminders_id', referencedColumnName: 'id', nullable: false)]
    private ?Reminder $reminder;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'entityReminders')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

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

    /**
     * Get the value of reminder
     */
    public function getReminder()
    {
        return $this->reminder;
    }

    /**
     * Set the value of reminder
     *
     * @return  self
     */
    public function setReminder($reminder)
    {
        $this->reminder = $reminder;

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
}
