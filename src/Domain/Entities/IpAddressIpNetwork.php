<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ipaddresses_ipnetworks")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["ipaddresses_id", "ipnetworks_id"])]
#[ORM\Index(name: "ipnetworks_id", columns: ["ipnetworks_id"])]
#[ORM\Index(name: "ipaddresses_id", columns: ["ipaddresses_id"])]
class IpAddressIpNetwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: IpAddress::class, inversedBy: 'ipaddressIpnetworks')]
    #[ORM\JoinColumn(name: 'ipaddresses_id', referencedColumnName: 'id', nullable: true)]
    private ?IpAddress $ipaddress = null;

    #[ORM\ManyToOne(targetEntity: IpNetwork::class, inversedBy: 'ipaddressIpnetworks')]
    #[ORM\JoinColumn(name: 'ipnetworks_id', referencedColumnName: 'id', nullable: true)]
    private ?IpNetwork $ipnetwork = null;

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
     * Get the value of ipaddress
     */
    public function getIpaddress()
    {
        return $this->ipaddress;
    }

    /**
     * Set the value of ipaddress
     *
     * @return  self
     */
    public function setIpaddress($ipaddress)
    {
        $this->ipaddress = $ipaddress;

        return $this;
    }
}
