<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_savedsearches')]
#[ORM\Index(name: "type", columns: ["type"])]
#[ORM\Index(name: "itemtype", columns: ["itemtype"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "is_private", columns: ["is_private"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "last_execution_time", columns: ["last_execution_time"])]
#[ORM\Index(name: "last_execution_date", columns: ["last_execution_date"])]
#[ORM\Index(name: "do_count", columns: ["do_count"])]
class Savedsearch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'see SavedSearch:: constants'])]
    private $type;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_private;

    #[ORM\Column(type: 'integer', options: ['default' => -1])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $path;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $query;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $last_execution_time;

    #[ORM\Column(type: 'boolean', options: ['default' => 2, 'comment' => 'Do or do not count results on list display see SavedSearch::COUNT_* constants'])]
    private $do_count;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $last_execution_date;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $counter;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->is_private;
    }

    public function setIsPrivate(bool $is_private): self
    {
        $this->is_private = $is_private;

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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getLastExecutionTime(): ?\DateTimeInterface
    {
        return $this->last_execution_time;
    }

    public function setLastExecutionTime(\DateTimeInterface $last_execution_time): self
    {
        $this->last_execution_time = $last_execution_time;

        return $this;
    }

    public function getDoCount(): ?bool
    {
        return $this->do_count;
    }

    public function setDoCount(bool $do_count): self
    {
        $this->do_count = $do_count;

        return $this;
    }

    public function getLastExecutionDate(): ?\DateTimeInterface
    {
        return $this->last_execution_date;
    }

    public function setLastExecutionDate(\DateTimeInterface $last_execution_date): self
    {
        $this->last_execution_date = $last_execution_date;

        return $this;
    }

    public function getCounter(): ?int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): self
    {
        $this->counter = $counter;

        return $this;
    }       

}   
