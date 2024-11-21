<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_cartridges')]
#[ORM\Index(name: 'cartridgeitems_id', columns: ['cartridgeitems_id'])]
#[ORM\Index(name: 'printers_id', columns: ['printers_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Cartridge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $cartridgeitems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $printers_id;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date_in;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date_use;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date_out;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $pages;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getCartridgeitemsId(): ?int
    {
        return $this->cartridgeitems_id;
    }

    public function setCartridgeitemsId(int $cartridgeitems_id): self
    {
        $this->cartridgeitems_id = $cartridgeitems_id;

        return $this;
    }

    public function getPrintersId(): ?int
    {
        return $this->printers_id;
    }

    public function setPrintersId(int $printers_id): self
    {
        $this->printers_id = $printers_id;

        return $this;
    }

    public function getDateIn(): ?\DateTimeInterface
    {
        return $this->date_in;
    }

    public function setDateIn(\DateTimeInterface $date_in): self
    {
        $this->date_in = $date_in;

        return $this;
    }

    public function getDateUse(): ?\DateTimeInterface
    {
        return $this->date_use;
    }

    public function setDateUse(\DateTimeInterface $date_use): self
    {
        $this->date_use = $date_use;

        return $this;
    }

    public function getDateOut(): ?\DateTimeInterface
    {
        return $this->date_out;
    }

    public function setDateOut(\DateTimeInterface $date_out): self
    {
        $this->date_out = $date_out;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->pages;
    }

    public function setPages(int $pages): self
    {
        $this->pages = $pages;

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
