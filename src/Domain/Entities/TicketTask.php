<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickettasks")]
#[ORM\UniqueConstraint(name: "uuid", columns: ["uuid"])]
#[ORM\Index(name: "date", columns: ["date"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "users_id_editor", columns: ["users_id_editor"])]
#[ORM\Index(name: "tickets_id", columns: ["tickets_id"])]
#[ORM\Index(name: "is_private", columns: ["is_private"])]
#[ORM\Index(name: "taskcategories_id", columns: ["taskcategories_id"])]
#[ORM\Index(name: "state", columns: ["state"])]
#[ORM\Index(name: "users_id_tech", columns: ["users_id_tech"])]
#[ORM\Index(name: "groups_id_tech", columns: ["groups_id_tech"])]
#[ORM\Index(name: "begin", columns: ["begin"])]
#[ORM\Index(name: "end", columns: ["end"])]
#[ORM\Index(name: "tasktemplates_id", columns: ["tasktemplates_id"])]
#[ORM\Index(name: "sourceitems_id", columns: ["sourceitems_id"])]
class TicketTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\Column(name: 'tickets_id', type: 'integer', options: ['default' => 0])]
    private $ticketsId;

    #[ORM\Column(name: 'taskcategories_id', type: 'integer', options: ['default' => 0])]
    private $taskcategoriesId;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(name: 'users_id', type: 'integer', options: ['default' => 0])]
    private $usersId;

    #[ORM\Column(name: 'users_id_editor', type: 'integer', options: ['default' => 0])]
    private $usersIdEditor;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(name: 'is_private', type: 'boolean', options: ['default' => 0])]
    private $isPrivate;

    #[ORM\Column(name: 'actiontime', type: 'integer', options: ['default' => 0])]
    private $actiontime;

    #[ORM\Column(name: 'begin', type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(name: 'end', type: 'datetime', nullable: true)]
    private $end;

    #[ORM\Column(name: 'state', type: 'integer', options: ['default' => 1])]
    private $state;

    #[ORM\Column(name: 'users_id_tech', type: 'integer', options: ['default' => 0])]
    private $usersIdTech;

    #[ORM\Column(name: 'groups_id_tech', type: 'integer', options: ['default' => 0])]
    private $groupsIdTech;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'tasktemplates_id', type: 'integer', options: ['default' => 0])]
    private $tasktemplatesId;

    #[ORM\Column(name: 'timeline_position', type: 'boolean', options: ['default' => 0])]
    private $timelinePosition;

    #[ORM\Column(name: 'sourceitems_id', type: 'integer', options: ['default' => 0])]
    private $sourceitemsId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getTicketsId(): ?int
    {
        return $this->ticketsId;
    }

    public function setTicketsId(?int $ticketsId): self
    {
        $this->ticketsId = $ticketsId;

        return $this;
    }

    public function getTaskcategoriesId(): ?int
    {
        return $this->taskcategoriesId;
    }

    public function setTaskcategoriesId(?int $taskcategoriesId): self
    {
        $this->taskcategoriesId = $taskcategoriesId;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->usersId;
    }

    public function setUsersId(?int $usersId): self
    {
        $this->usersId = $usersId;

        return $this;
    }

    public function getUsersIdEditor(): ?int
    {
        return $this->usersIdEditor;
    }

    public function setUsersIdEditor(?int $usersIdEditor): self
    {
        $this->usersIdEditor = $usersIdEditor;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(?bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getActiontime(): ?int
    {
        return $this->actiontime;
    }

    public function setActiontime(?int $actiontime): self
    {
        $this->actiontime = $actiontime;

        return $this;
    }

    public function getBegin(): ?\DateTime
    {
        return $this->begin;
    }

    public function setBegin(?\DateTime $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(?\DateTime $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getUsersIdTech(): ?int
    {
        return $this->usersIdTech;
    }

    public function setUsersIdTech(?int $usersIdTech): self
    {
        $this->usersIdTech = $usersIdTech;

        return $this;
    }

    public function getGroupsIdTech(): ?int
    {
        return $this->groupsIdTech;
    }

    public function setGroupsIdTech(?int $groupsIdTech): self
    {
        $this->groupsIdTech = $groupsIdTech;

        return $this;
    }

    public function getDateMod(): ?\DateTime
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTime $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getTasktemplatesId(): ?int
    {
        return $this->tasktemplatesId;
    }

    public function setTasktemplatesId(?int $tasktemplatesId): self
    {
        $this->tasktemplatesId = $tasktemplatesId;

        return $this;
    }

    public function getTimelinePosition(): ?bool
    {
        return $this->timelinePosition;
    }

    public function setTimelinePosition(?bool $timelinePosition): self
    {
        $this->timelinePosition = $timelinePosition;

        return $this;
    }

    public function getSourceitemsId(): ?int
    {
        return $this->sourceitemsId;
    }

    public function setSourceitemsId(?int $sourceitemsId): self
    {
        $this->sourceitemsId = $sourceitemsId;

        return $this;
    }

}
