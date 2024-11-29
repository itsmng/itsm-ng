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

    #[ORM\Column(type: "integer", name: 'ipnetworks_id', options: ["default" => 0])]
    private $ipnetworks_id;

    #[ORM\ManyToOne(targetEntity: IpNetwork::class, inversedBy: 'ipnetworkVlans')]
    #[ORM\JoinColumn(name: 'ipaddresses_id', referencedColumnName: 'id', nullable: false)]
    private ?IpNetwork $ipnetwork;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $vlans_id;

    #[ORM\ManyToOne(targetEntity: Vlan::class, inversedBy: 'ipnetworkVlans')]
    #[ORM\JoinColumn(name: 'vlans_id', referencedColumnName: 'id', nullable: false)]
    private ?Vlan $vlan;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpnetworksId(): ?int
    {
        return $this->ipnetworks_id;
    }

    public function setIpnetworksId(int $ipnetworks_id): self
    {
        $this->ipnetworks_id = $ipnetworks_id;

        return $this;
    }

    public function getVlansId(): ?int
    {
        return $this->vlans_id;
    }

    public function setVlansId(int $vlans_id): self
    {
        $this->vlans_id = $vlans_id;

        return $this;
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
