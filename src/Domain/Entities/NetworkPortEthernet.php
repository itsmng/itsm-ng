<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_networkportethernets')]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Index(name: 'card', columns: ['items_devicenetworkcards_id'])]
#[ORM\Index(name: 'netpoint', columns: ['netpoints_id'])]
#[ORM\Index(name: 'type', columns: ['type'])]
#[ORM\Index(name: 'speed', columns: ['speed'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class NetworkPortEthernet
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
    private ?ItemDeviceNetworkCard $itemDevicenetworkcard = null;

    #[ORM\ManyToOne(targetEntity: Netpoint::class)]
    #[ORM\JoinColumn(name: 'netpoints_id', referencedColumnName: 'id', nullable: true)]
    private ?Netpoint $netpoint = null;

    #[ORM\Column(name: 'type', type: 'string', length: 10, nullable: true, options: ['default' => '', 'comment' => 'T, LX, SX'])]
    private $type = '';

    #[ORM\Column(name: 'speed', type: 'integer', options: ['default' => 10, 'comment' => 'Mbit/s: 10, 100, 1000, 10000'])]
    private $speed = 10;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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
     * Get the value of netpoint
     */
    public function getNetpoint()
    {
        return $this->netpoint;
    }

    /**
     * Set the value of netpoint
     *
     * @return  self
     */
    public function setNetpoint($netpoint)
    {
        $this->netpoint = $netpoint;

        return $this;
    }


    /**
     * Get the value of itemsDevicenetworkcard
     */
    // public function getItemsDevicenetworkcard()
    // {
    //     return $this->itemsDevicenetworkcard;
    // }

    // /**
    //  * Set the value of itemsDevicenetworkcard
    //  *
    //  * @return  self
    //  */
    // public function setItemsDevicenetworkcard($itemsDevicenetworkcard)
    // {
    //     $this->itemsDevicenetworkcard = $itemsDevicenetworkcard;

    //     return $this;
    // }

    /**
     * Get the value of itemDevicenetworkcard
     */ 
    public function getItemDevicenetworkcard()
    {
        return $this->itemDevicenetworkcard;
    }

    /**
     * Set the value of itemDevicenetworkcard
     *
     * @return  self
     */ 
    public function setItemDevicenetworkcard($itemDevicenetworkcard)
    {
        $this->itemDevicenetworkcard = $itemDevicenetworkcard;

        return $this;
    }
}
