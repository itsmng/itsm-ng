<?php

namespace App\Domain\Entities;

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_pdus_plugs')]
#[ORM\Index(name: "plugs_id", columns: ["plugs_id"])]
#[ORM\Index(name: "pdus_id", columns: ["pdus_id"])]
class PduPlug {

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $plugs_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $pdus_id;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    private $number_plugs;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int {
        return $this->id;
    }

    public function getPlugsId(): ?int {
        return $this->plugs_id;
    }

    public function setPlugsId(int $plugs_id): self {
        $this->plugs_id = $plugs_id;

        return $this;
    }

    public function getPdusId(): ?int {
        return $this->pdus_id;
    }

    public function setPdusId(int $pdus_id): self {
        $this->pdus_id = $pdus_id;

        return $this;
    }

    public function getNumberPlugs(): ?int {
        return $this->number_plugs;
    }

    public function setNumberPlugs(int $number_plugs): self {
        $this->number_plugs = $number_plugs;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self {
        $this->date_creation = $date_creation;

        return $this;
    }
}
