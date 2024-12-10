<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkportwifis')]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Index(name: 'card', columns: ['items_devicenetworkcards_id'])]
#[ORM\Index(name: 'essid', columns: ['wifinetworks_id'])]
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

    #[ORM\ManyToOne(targetEntity: NetworkPort::class)]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?NetworkPort $networkport;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_devicenetworkcards_id;

    #[ORM\ManyToOne(targetEntity: WifiNetwork::class)]
    #[ORM\JoinColumn(name: 'wifinetworks_id', referencedColumnName: 'id', nullable: true)]
    private ?WifiNetwork $wifinetwork;

    #[ORM\ManyToOne(targetEntity: Networkportwifi::class)]
    #[ORM\JoinColumn(name: 'networkportwifis_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'only useful in case of Managed node'])]
    private ?Networkportwifi $networkportwifi;

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

    public function getItemsDevicenetworkcardsId(): ?int
    {
        return $this->items_devicenetworkcards_id;
    }

    public function setItemsDevicenetworkcardsId(?int $items_devicenetworkcards_id): self
    {
        $this->items_devicenetworkcards_id = $items_devicenetworkcards_id;

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


    /**
     * Get the value of networkportwifi
     */ 
    public function getNetworkportwifi()
    {
        return $this->networkportwifi;
    }

    /**
     * Set the value of networkportwifi
     *
     * @return  self
     */ 
    public function setNetworkportwifi($networkportwifi)
    {
        $this->networkportwifi = $networkportwifi;

        return $this;
    }

    /**
     * Get the value of wifinetwork
     */ 
    public function getWifinetwork()
    {
        return $this->wifinetwork;
    }

    /**
     * Set the value of wifinetwork
     *
     * @return  self
     */ 
    public function setWifinetwork($wifinetwork)
    {
        $this->wifinetwork = $wifinetwork;

        return $this;
    }

    /**
     * Get the value of networkport
     */ 
    public function getNetworkport()
    {
        return $this->networkport;
    }

    /**
     * Set the value of networkport
     *
     * @return  self
     */ 
    public function setNetworkport($networkport)
    {
        $this->networkport = $networkport;

        return $this;
    }
}
