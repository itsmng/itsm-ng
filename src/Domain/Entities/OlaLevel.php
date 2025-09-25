<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_olalevels')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'is_active', columns: ['is_active'])]
#[ORM\Index(name: 'olas_id', columns: ['olas_id'])]
class OlaLevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Ola::class)]
    #[ORM\JoinColumn(name: 'olas_id', referencedColumnName: 'id', nullable: true)]
    private ?Ola $ola = null;

    #[ORM\Column(name: 'execution_time', type: 'integer')]
    private $executionTime;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 1])]
    private $isActive = true;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive = false;

    #[ORM\Column(name: 'match', type: 'string', length: 10, nullable: true, options: ['comment' => 'see define.php *_MATCHING constant'])]
    private $match;

    #[ORM\Column(name: 'uuid', type: 'string', length: 255, nullable: true)]
    private $uuid;

    #[ORM\OneToMany(mappedBy: 'olalevel', targetEntity: OlaLevelTicket::class)]
    private Collection $olalevelTickets;


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

    public function getExecutionTime(): ?int
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?int $executionTime): self
    {
        $this->executionTime = $executionTime;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getMatch(): ?string
    {
        return $this->match;
    }

    public function setMatch(?string $match): self
    {
        $this->match = $match;

        return $this;
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

    /**
     * Get the value of ola
     */
    public function getOla()
    {
        return $this->ola;
    }

    /**
     * Set the value of ola
     *
     * @return  self
     */
    public function setOla($ola)
    {
        $this->ola = $ola;

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
     * Get the value of olalevelTickets
     */
    public function getOlaLevelTickets()
    {
        return $this->olalevelTickets;
    }

    /**
     * Set the value of olalevelTickets
     *
     * @return  self
     */
    public function setOlaLevelTickets($olalevelTickets)
    {
        $this->olalevelTickets = $olalevelTickets;

        return $this;
    }
}
