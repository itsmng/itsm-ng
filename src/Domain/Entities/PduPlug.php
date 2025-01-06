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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Plug::class, inversedBy: 'pduPlugs')]
    #[ORM\JoinColumn(name: 'plugs_id', referencedColumnName: 'id', nullable: true)]
    private ?Plug $plug;

    #[ORM\ManyToOne(targetEntity: Pdu::class, inversedBy: 'pduPlugs')]
    #[ORM\JoinColumn(name: 'pdus_id', referencedColumnName: 'id', nullable: true)]
    private ?Pdu $pdu;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    private $number_plugs;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumberPlugs(): ?int
    {
        return $this->number_plugs;
    }

    public function setNumberPlugs(int $number_plugs): self
    {
        $this->number_plugs = $number_plugs;

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
