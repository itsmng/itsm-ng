<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_pdus_racks')]
#[ORM\Index(name: "racks_id", columns: ["racks_id"])]
#[ORM\Index(name: "pdus_id", columns: ["pdus_id"])]
class PduRack {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $racks_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $pdus_id;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    private $side;

    #[ORM\Column(type: 'integer')]
    private $position;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private $bgcolor;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRacksId(): ?int
    {
        return $this->racks_id;
    }

    public function setRacksId(?int $racks_id): self
    {
        $this->racks_id = $racks_id;

        return $this;
    }

    public function getPdusId(): ?int
    {
        return $this->pdus_id;
    }

    public function setPdusId(?int $pdus_id): self
    {
        $this->pdus_id = $pdus_id;

        return $this;
    }

    public function getSide(): ?int
    {
        return $this->side;
    }

    public function setSide(?int $side): self
    {
        $this->side = $side;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getBgcolor(): ?string
    {
        return $this->bgcolor;
    }

    public function setBgcolor(?string $bgcolor): self
    {
        $this->bgcolor = $bgcolor;

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
