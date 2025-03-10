<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_softwareversions")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "softwares_id", columns: ["softwares_id"])]
#[ORM\Index(name: "states_id", columns: ["states_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "operatingsystems_id", columns: ["operatingsystems_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class SoftwareVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: Software::class)]
    #[ORM\JoinColumn(name: 'softwares_id', referencedColumnName: 'id', nullable: true)]
    private ?Software $software = null;

    #[ORM\Column(name: 'states_id', type: 'integer', options: ['default' => 0])]
    private $statesId;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'operatingsystems_id', type: 'integer', nullable: true)]
    private $operatingsystemsId;

    #[ORM\ManyToOne(targetEntity: Operatingsystem::class)]
    #[ORM\JoinColumn(name: 'operatingsystems_id', referencedColumnName: 'id', nullable: true)]
    private ?Operatingsystem $operatingsystem = null;


    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatesId(): ?int
    {
        return $this->statesId;
    }

    public function setStatesId(?int $statesId): self
    {
        $this->statesId = $statesId;

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

    public function getOperatingsystemsId(): ?int
    {
        return $this->operatingsystemsId;
    }

    public function setOperatingsystemsId(?int $operatingsystemsId): self
    {
        $this->operatingsystemsId = $operatingsystemsId;

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
     * Get the value of software
     */
    public function getSoftware()
    {
        return $this->software;
    }

    /**
     * Set the value of software
     *
     * @return  self
     */
    public function setSoftware($software)
    {
        $this->software = $software;

        return $this;
    }

    /**
     * Get the value of operatingsystem
     */
    public function getOperatingsystem()
    {
        return $this->operatingsystem;
    }

    /**
     * Set the value of operatingsystem
     *
     * @return  self
     */
    public function setOperatingsystem($operatingsystem)
    {
        $this->operatingsystem = $operatingsystem;

        return $this;
    }
}
