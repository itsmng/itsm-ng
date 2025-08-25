<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use NetworkPort;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkports_vlans')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['networkports_id', 'vlans_id'])]
#[ORM\Index(name: 'vlans_id', columns: ['vlans_id'])]
class NetworkPortVlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: NetworkPort::class, inversedBy: 'networkportVlans')]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?NetworkPort $networkport = null;

    #[ORM\ManyToOne(targetEntity: Vlan::class, inversedBy: 'networkportVlans')]
    #[ORM\JoinColumn(name: 'vlans_id', referencedColumnName: 'id', nullable: true)]
    private ?Vlan $vlan = null;

    #[ORM\Column(name: 'tagged', type: 'boolean', options: ['default' => 0])]
    private $tagged = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTagged(): ?bool
    {
        return $this->tagged;
    }

    public function setTagged(?bool $tagged): self
    {
        $this->tagged = $tagged;

        return $this;
    }


    /**
     * Get the value of vlan
     */
    public function getVlan()
    {
        return $this->vlan;
    }

    /**
     * Set the value of vlan
     *
     * @return  self
     */
    public function setVlan($vlan)
    {
        $this->vlan = $vlan;

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
