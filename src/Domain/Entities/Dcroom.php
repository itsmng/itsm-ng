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
class Dcroom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'locations_id', referencedColumnName: 'id', nullable: true)]
    private ?Location $location;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $vis_cols;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $vis_rows;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $blueprint;

    #[ORM\ManyToOne(targetEntity: Datacenter::class)]
    #[ORM\JoinColumn(name: 'datacenters_id', referencedColumnName: 'id', nullable: true)]
    private ?Datacenter $datacenter;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

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
        return $this->is_recursive;
    }

    public function setIsRecursive(int $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    
    public function getVisCols(): ?int
    {
        return $this->vis_cols;
    }

    public function setVisCols(int $vis_cols): self
    {
        $this->vis_cols = $vis_cols;

        return $this;
    }

    public function getVisRows(): ?int
    {
        return $this->vis_rows;
    }

    public function setVisRows(int $vis_rows): self
    {
        $this->vis_rows = $vis_rows;

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
        return $this->is_deleted;
    }

    public function setIsDeleted(int $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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
