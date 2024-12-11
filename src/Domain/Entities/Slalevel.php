<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_slalevels')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "slas_id", columns: ["slas_id"])]
class Slalevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Sla::class)]
    #[ORM\JoinColumn(name: 'slas_id', referencedColumnName: 'id', nullable: true)]
    private ?Sla $sla;

    #[ORM\Column(type: 'integer')]
    private $execution_time;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private $is_active;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 10, nullable: true, options: ['comment' => 'see define.php *_MATCHING constant'])]
    private $match;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $uuid;

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

    public function getExecutionTime(): ?int
    {
        return $this->execution_time;
    }

    public function setExecutionTime(int $execution_time): self
    {
        $this->execution_time = $execution_time;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

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


    public function getMatch(): ?string
    {
        return $this->match;
    }

    public function setMatch(string $match): self
    {
        $this->match = $match;

        return $this;
    }


    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }


    /**
     * Get the value of sla
     */ 
    public function getSla()
    {
        return $this->sla;
    }

    /**
     * Set the value of sla
     *
     * @return  self
     */ 
    public function setSla($sla)
    {
        $this->sla = $sla;

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
