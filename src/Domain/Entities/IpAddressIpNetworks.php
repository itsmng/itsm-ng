<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ipaddresses_ipnetworks")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["ipaddresses_id", "ipnetworks_id"])]
#[ORM\Index(name: "ipnetworks_id", columns: ["ipnetworks_id"])]
#[ORM\Index(name: "ipaddresses_id", columns: ["ipaddresses_id"])]
class IpAddressIpNetworks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $ipaddresses_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $ipnetworks_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpaddressesId(): ?int
    {
        return $this->ipaddresses_id;
    }

    public function setIpaddressesId(int $ipaddresses_id): self
    {
        $this->ipaddresses_id = $ipaddresses_id;

        return $this;
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
}
