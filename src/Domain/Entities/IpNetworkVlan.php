<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ipnetworks_vlans")]
#[ORM\UniqueConstraint(name: "link", columns: ["ipnetworks_id", "vlans_id"])]
class IpNetworkVlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: IpNetwork::class, inversedBy: 'ipnetworkVlans')]
    #[ORM\JoinColumn(name: 'ipnetworks_id', referencedColumnName: 'id', nullable: true)]
    private ?IpNetwork $ipnetwork;

    #[ORM\ManyToOne(targetEntity: Vlan::class, inversedBy: 'ipnetworkVlans')]
    #[ORM\JoinColumn(name: 'vlans_id', referencedColumnName: 'id', nullable: true)]
    private ?Vlan $vlan;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of ipnetwork
     */
    public function getIpnetwork()
    {
        return $this->ipnetwork;
    }

    /**
     * Set the value of ipnetwork
     *
     * @return  self
     */
    public function setIpnetwork($ipnetwork)
    {
        $this->ipnetwork = $ipnetwork;

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
}
