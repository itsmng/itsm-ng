<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

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
class SavedSearch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'type', type: 'integer', options: ['default' => 0, 'comment' => 'see SavedSearch:: constants'])]
    private $type;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'is_private', type: 'boolean', options: ['default' => 1])]
    private $isPrivate;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'path', type: 'string', length: 255, nullable: true)]
    private $path;

    #[ORM\Column(name: 'query', type: 'text', length: 65535, nullable: true)]
    private $query;

    #[ORM\Column(name: 'last_execution_time', type: 'integer', nullable: true)]
    private $lastExecutionTime;

    #[ORM\Column(name: 'do_count', type: 'boolean', options: ['default' => 2, 'comment' => 'Do or do not count results on list display see SavedSearch::COUNT_* constants'])]
    private $doCount;

    #[ORM\Column(name: 'last_execution_date', type: 'datetime', nullable: true)]
    private $lastExecutionDate;

    #[ORM\Column(name: 'counter', type: 'integer', options: ['default' => 0])]
    private $counter;

    #[ORM\OneToMany(mappedBy: 'savedsearch', targetEntity: SavedSearchAlert::class)]
    private Collection $savedsearchAlerts;

    #[ORM\OneToMany(mappedBy: 'savedsearch', targetEntity: SavedSearchUser::class)]
    private Collection $savedsearchUsers;

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

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->lastExecutionTime;
    }

    public function setLastExecutionTime(\DateTimeInterface $lastExecutionTime): self
    {
        $this->lastExecutionTime = $lastExecutionTime;

        return $this;
    }

    public function getDoCount(): ?bool
    {
        return $this->doCount;
    }

    public function setDoCount(bool $doCount): self
    {
        $this->doCount = $doCount;

        return $this;
    }

    public function getLastExecutionDate(): ?\DateTimeInterface
    {
        return $this->lastExecutionDate;
    }

    public function setLastExecutionDate(\DateTimeInterface $lastExecutionDate): self
    {
        $this->lastExecutionDate = $lastExecutionDate;

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

    /**
     * Get the value of savedsearchAlerts
     */
    public function getSavedSearchAlerts()
    {
        return $this->savedsearchAlerts;
    }

    /**
     * Set the value of savedsearchAlerts
     *
     * @return  self
     */
    public function setSavedSearchAlerts($savedsearchAlerts)
    {
        $this->savedsearchAlerts = $savedsearchAlerts;

        return $this;
    }

    /**
     * Get the value of savedsearchUsers
     */
    public function getSavedSearchUsers()
    {
        return $this->savedsearchUsers;
    }

    /**
     * Set the value of savedsearchUsers
     *
     * @return  self
     */
    public function setSavedSearchUsers($savedsearchUsers)
    {
        $this->savedsearchUsers = $savedsearchUsers;

        return $this;
    }
}
