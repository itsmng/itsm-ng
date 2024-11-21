<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkportwifis')]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Index(name: 'items_devicenetworkcards_id', columns: ['items_devicenetworkcards_id'])]
#[ORM\Index(name: 'wifinetworks_id', columns: ['wifinetworks_id'])]
#[ORM\Index(name: 'version', columns: ['version'])]
#[ORM\Index(name: 'mode', columns: ['mode'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Networkportwifi
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
    private $wifinetworks_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'only useful in case of Managed node'])]
    private $networkportwifis_id;

    #[ORM\Column(type: 'string', length: 20, nullable: true, options: ['comment' => 'a, a/b, a/b/g, a/b/g/n, a/b/g/n/y'])]
    private $version;

    #[ORM\Column(type: 'string', length: 20, nullable: true, options: ['comment' => 'ad-hoc, managed, master, repeater, secondary, monitor, auto'])]
    private $mode;

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

    public function getWifinetworksId(): ?int
    {
        return $this->wifinetworks_id;
    }

    public function setWifinetworksId(?int $wifinetworks_id): self
    {
        $this->wifinetworks_id = $wifinetworks_id;

        return $this;
    }

    public function getNetworkportwifisId(): ?int
    {
        return $this->networkportwifis_id;
    }

    public function setNetworkportwifisId(?int $networkportwifis_id): self
    {
        $this->networkportwifis_id = $networkportwifis_id;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): self
    {
        $this->mode = $mode;

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
