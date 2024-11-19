<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name:'networkports_id', columns: ['networkports_id'])]
#[ORM\Table(name: 'glpi_networkportfiberchannels')]
#[ORM\Index(name: 'items_devicenetworkcards_id', columns: ['items_devicenetworkcards_id'])]
#[ORM\Index(name: 'netpoints_id', columns: ['netpoints_id'])]
#[ORM\Index(name: 'wwn', columns: ['wwn'])]
#[ORM\Index(name: 'speed', columns: ['speed'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Networkportfiberchannel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $networkports_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_devicenetworkcards_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $netpoints_id;

    #[ORM\Column(type: 'string', length: 16, options: ['default' => ''], nullable: true)]
    private $wwn;

    #[ORM\Column(type: 'integer', options: ['default' => 10, 'comment' => 'Mbit/s: 10, 100, 1000, 10000'])]
    private $speed;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNetworkportsId(): ?int
    {
        return $this->networkports_id;
    }

    public function setNetworkportsId(?int $networkports_id): self
    {
        $this->networkports_id = $networkports_id;

        return $this;
    }

    public function getItemsDevicenetworkcardsId(): ?int
    {
        return $this->items_devicenetworkcards_id;
    }

    public function setItemsDevicenetworkcardsId(?int $items_devicenetworkcards_id): self
    {
        $this->items_devicenetworkcards_id = $items_devicenetworkcards_id;

        return $this;
    }

    public function getNetpointsId(): ?int
    {
        return $this->netpoints_id;
    }

    public function setNetpointsId(?int $netpoints_id): self
    {
        $this->netpoints_id = $netpoints_id;

        return $this;
    }

    public function getWwn(): ?string
    {
        return $this->wwn;
    }

    public function setWwn(?string $wwn): self
    {
        $this->wwn = $wwn;

        return $this;
    }

    public function getSpeed(): ?int
    {
        return $this->speed;
    }

    public function setSpeed(?int $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

}
