<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name:'networkports_id', columns: ['networkports_id'])]
#[ORM\Table(name: 'glpi_networkportfiberchannels')]
#[ORM\Index(name: 'card', columns: ['items_devicenetworkcards_id'])]
#[ORM\Index(name: 'netpoint', columns: ['netpoints_id'])]
#[ORM\Index(name: 'wwn', columns: ['wwn'])]
#[ORM\Index(name: 'speed', columns: ['speed'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class NetworkPortFiberChannel
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


    #[ORM\ManyToOne(targetEntity: Netpoint::class)]
    #[ORM\JoinColumn(name: 'netpoints_id', referencedColumnName: 'id', nullable: true)]
    private ?Netpoint $netpoint = null;

    #[ORM\Column(name: 'wwn', type: 'string', length: 16, options: ['default' => ''], nullable: true)]
    private $wwn;

    #[ORM\Column(name: 'speed', type: 'integer', options: ['default' => 10, 'comment' => 'Mbit/s: 10, 100, 1000, 10000'])]
    private $speed;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
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
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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
