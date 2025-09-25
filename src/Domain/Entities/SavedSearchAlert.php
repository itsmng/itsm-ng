<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_savedsearches_alerts')]
#[ORM\UniqueConstraint(name: "unicity", columns: ["savedsearches_id", "operator", "value"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class SavedSearchAlert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: SavedSearch::class, inversedBy: 'savedsearchAlerts')]
    #[ORM\JoinColumn(name: 'savedsearches_id', referencedColumnName: 'id', nullable: true)]
    private ?SavedSearch $savedsearch = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 0])]
    private $isActive = false;

    #[ORM\Column(name: 'operator', type: 'boolean')]
    private $operator = false;

    #[ORM\Column(name: 'value', type: 'integer')]
    private $value;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getOperator(): ?bool
    {
        return $this->operator;
    }

    public function setOperator(bool $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

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
     * Get the value of savedsearch
     */
    public function getSavedSearch()
    {
        return $this->savedsearch;
    }

    /**
     * Set the value of savedsearch
     *
     * @return  self
     */
    public function setSavedSearch($savedsearch)
    {
        $this->savedsearch = $savedsearch;

        return $this;
    }
}
