<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_dcrooms')]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'locations_id', columns: ['locations_id'])]
#[ORM\Index(name: 'datacenters_id', columns: ['datacenters_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Dcroom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location = null;

    #[ORM\Column(name: 'vis_cols', type: 'integer', nullable: true)]
    private $visCols;

    #[ORM\Column(name: 'vis_rows', type: 'integer', nullable: true)]
    private $visRows;

    #[ORM\Column(name: 'blueprint', type: 'text', length: 65535, nullable: true)]
    private $blueprint;

    #[ORM\ManyToOne(targetEntity: Datacenter::class)]
    #[ORM\JoinColumn(name: 'datacenters_id', referencedColumnName: 'id', nullable: true)]
    private ?Datacenter $datacenter = null;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

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


    public function getIsRecursive(): ?int
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(int $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }


    public function getVisCols(): ?int
    {
        return $this->visCols;
    }

    public function setVisCols(int $visCols): self
    {
        $this->visCols = $visCols;

        return $this;
    }

    public function getVisRows(): ?int
    {
        return $this->visRows;
    }

    public function setVisRows(int $visRows): self
    {
        $this->visRows = $visRows;

        return $this;
    }

    public function getBlueprint(): ?string
    {
        return $this->blueprint;
    }

    public function setBlueprint(?string $blueprint): self
    {
        $this->blueprint = $blueprint;

        return $this;
    }


    public function getIsDeleted(): ?int
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(int $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
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
     * Get the value of location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of datacenter
     */
    public function getDatacenter()
    {
        return $this->datacenter;
    }

    /**
     * Set the value of datacenter
     *
     * @return  self
     */
    public function setDatacenter($datacenter)
    {
        $this->datacenter = $datacenter;

        return $this;
    }
}
