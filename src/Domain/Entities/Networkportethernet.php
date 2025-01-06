<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkportethernets')]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Index(name: 'card', columns: ['items_devicenetworkcards_id'])]
#[ORM\Index(name: 'netpoint', columns: ['netpoints_id'])]
#[ORM\Index(name: 'type', columns: ['type'])]
#[ORM\Index(name: 'speed', columns: ['speed'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Networkportethernet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Networkport::class)]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?Networkport $networkport;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_devicenetworkcards_id;

    #[ORM\ManyToOne(targetEntity: Netpoint::class)]
    #[ORM\JoinColumn(name: 'netpoints_id', referencedColumnName: 'id', nullable: true)]
    private ?Netpoint $netpoint;

    #[ORM\Column(type: 'string', length: 10, nullable: true, options: ['default' => '', 'comment' => 'T, LX, SX'])]
    private $type;

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

    public function getItemsDevicenetworkcardsId(): ?int
    {
        return $this->items_devicenetworkcards_id;
    }

    public function setItemsDevicenetworkcardsId(?int $items_devicenetworkcards_id): self
    {
        $this->items_devicenetworkcards_id = $items_devicenetworkcards_id;

        return $this;
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
}
