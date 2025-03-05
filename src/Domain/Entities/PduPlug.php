<?php

namespace App\Domain\Entities;

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_pdus_plugs')]
#[ORM\Index(name: "plugs_id", columns: ["plugs_id"])]
#[ORM\Index(name: "pdus_id", columns: ["pdus_id"])]
class PduPlug
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Plug::class, inversedBy: 'pduPlugs')]
    #[ORM\JoinColumn(name: 'plugs_id', referencedColumnName: 'id', nullable: true)]
    private ?Plug $plug = null;

    #[ORM\ManyToOne(targetEntity: Pdu::class, inversedBy: 'pduPlugs')]
    #[ORM\JoinColumn(name: 'pdus_id', referencedColumnName: 'id', nullable: true)]
    private ?Pdu $pdu = null;

    #[ORM\Column(name: 'number_plugs', type: 'integer', nullable: true, options: ['default' => 0])]
    private $numberPlugs;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumberPlugs(): ?int
    {
        return $this->numberPlugs;
    }

    public function setNumberPlugs(int $numberPlugs): self
    {
        $this->numberPlugs = $numberPlugs;

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
     * Get the value of plug
     */
    public function getPlug()
    {
        return $this->plug;
    }

    /**
     * Set the value of plug
     *
     * @return  self
     */
    public function setPlug($plug)
    {
        $this->plug = $plug;

        return $this;
    }

    /**
     * Get the value of pdu
     */
    public function getPdu()
    {
        return $this->pdu;
    }

    /**
     * Set the value of pdu
     *
     * @return  self
     */
    public function setPdu($pdu)
    {
        $this->pdu = $pdu;

        return $this;
    }
}
