<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_racktypes')]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
class Racktype
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?string
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?string $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->date_creation;
    }

    public function setDateCreation(?string $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateMod(): ?string
    {
        return $this->date_mod;
    }

    public function setDateMod(?string $date_mod): self
    {
        $this->date_mod = $date_mod;

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
