<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_networkportwifis')]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Index(name: 'card', columns: ['items_devicenetworkcards_id'])]
#[ORM\Index(name: 'essid', columns: ['wifinetworks_id'])]
#[ORM\Index(name: 'version', columns: ['version'])]
#[ORM\Index(name: 'mode', columns: ['mode'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class NetworkPortWifi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: NetworkPort::class)]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?NetworkPort $networkport = null;

    #[ORM\ManyToOne(targetEntity: ItemDeviceNetworkCard::class)]
    #[ORM\JoinColumn(name: 'items_devicenetworkcards_id', referencedColumnName: 'id', nullable: true)]
    private $itemsDevicenetworkcard = null;

    #[ORM\ManyToOne(targetEntity: WifiNetwork::class)]
    #[ORM\JoinColumn(name: 'wifinetworks_id', referencedColumnName: 'id', nullable: true)]
    private ?WifiNetwork $wifinetwork = null;

    #[ORM\ManyToOne(targetEntity: NetworkPortwifi::class)]
    #[ORM\JoinColumn(name: 'networkportwifis_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'only useful in case of Managed node'])]
    private ?NetworkPortwifi $networkportwifi = null;

    #[ORM\Column(name: 'version', type: 'string', length: 20, nullable: true, options: ['comment' => 'a, a/b, a/b/g, a/b/g/n, a/b/g/n/y'])]
    private $version;

    #[ORM\Column(name: 'mode', type: 'string', length: 20, nullable: true, options: ['comment' => 'ad-hoc, managed, master, repeater, secondary, monitor, auto'])]
    private $mode;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

        return $this;
    }

    /**
     * Get the value of networkportwifi
     */
    public function getNetworkPortwifi()
    {
        return $this->networkportwifi;
    }

    /**
     * Set the value of networkportwifi
     *
     * @return  self
     */
    public function setNetworkPortwifi($networkportwifi)
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
    public function getNetworkPort()
    {
        return $this->networkport;
    }

    /**
     * Set the value of networkport
     *
     * @return  self
     */
    public function setNetworkPort($networkport)
    {
        $this->networkport = $networkport;

        return $this;
    }

    /**
     * Get the value of itemsDevicenetworkcard
     */ 
    public function getItemsDevicenetworkcard()
    {
        return $this->itemsDevicenetworkcard;
    }

    /**
     * Set the value of itemsDevicenetworkcard
     *
     * @return  self
     */ 
    public function setItemsDevicenetworkcard($itemsDevicenetworkcard)
    {
        $this->itemsDevicenetworkcard = $itemsDevicenetworkcard;

        return $this;
    }
}
