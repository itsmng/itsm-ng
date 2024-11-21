<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_savedsearches_alerts')]
#[ORM\UniqueConstraint(name: "savedsearches_id_operator_value", columns: ["savedsearches_id", "operator", "value"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class SavedsearchAlert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $savedsearches_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_active;

    #[ORM\Column(type: 'boolean')]
    private $operator;

    #[ORM\Column(type: 'integer')]
    private $value;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSavedsearchesId(): ?int
    {
        return $this->savedsearches_id;
    }

    public function setSavedsearchesId(int $savedsearches_id): self
    {
        $this->savedsearches_id = $savedsearches_id;

        return $this;
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
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

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

}