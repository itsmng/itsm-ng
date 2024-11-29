<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "glpi_ipnetworks")]
#[ORM\Index(name: "network_definition", columns: ["entities_id", "address", "netmask"])]
#[ORM\Index(name: "address", columns: ["address_0", "address_1", "address_2", "address_3"])]
#[ORM\Index(name: "netmask", columns: ["netmask_0", "netmask_1", "netmask_2", "netmask_3"])]
#[ORM\Index(name: "gateway", columns: ["gateway_0", "gateway_1", "gateway_2", "gateway_3"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class IpNetwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $ipnetworks_id;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $level;

    #[ORM\Column(type: "text", nullable: true)]
    private $ancestors_cache;

    #[ORM\Column(type: "text", nullable: true)]
    private $sons_cache;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $addressable;

    #[ORM\Column(type: "smallint", nullable: true, options: ["unsigned" => true, "default" => 0])]
    private $version;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private $address;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address_0;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address_1;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address_2;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address_3;

    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private $netmask;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask_0;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask_1;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask_2;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask_3;

    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private $gateway;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway_0;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway_1;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway_2;

    #[ORM\Column(type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway_3;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;
    
    #[ORM\OneToMany(mappedBy: 'ipnetwork', targetEntity: IpAddressIpNetwork::class)]
    private Collection $ipaddressIpnetworks;


    public function __construct()
    {
        $this->date_mod = new \DateTime();
        $this->date_creation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getIpNetworksId(): ?int
    {
        return $this->ipnetworks_id;
    }

    public function setIpNetworksId(int $ipnetworks_id): self
    {
        $this->ipnetworks_id = $ipnetworks_id;

        return $this;
    }

    public function getCompletename(): ?string
    {
        return $this->completename;
    }

    public function setCompletename(?string $completename): self
    {
        $this->completename = $completename;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getAncestorsCache(): ?string
    {
        return $this->ancestors_cache;
    }

    public function setAncestorsCache(?string $ancestors_cache): self
    {
        $this->ancestors_cache = $ancestors_cache;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sons_cache;
    }

    public function setSonsCache(?string $sons_cache): self
    {
        $this->sons_cache = $sons_cache;

        return $this;
    }

    public function getAddressable(): ?bool
    {
        return $this->addressable;
    }

    public function setAddressable(bool $addressable): self
    {
        $this->addressable = $addressable;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress0(): ?int
    {
        return $this->address_0;
    }

    public function setAddress0(int $address_0): self
    {
        $this->address_0 = $address_0;

        return $this;
    }

    public function getAddress1(): ?int
    {
        return $this->address_1;
    }

    public function setAddress1(int $address_1): self
    {
        $this->address_1 = $address_1;

        return $this;
    }

    public function getAddress2(): ?int
    {
        return $this->address_2;
    }

    public function setAddress2(int $address_2): self
    {
        $this->address_2 = $address_2;

        return $this;
    }

    public function getAddress3(): ?int
    {
        return $this->address_3;
    }

    public function setAddress3(int $address_3): self
    {
        $this->address_3 = $address_3;

        return $this;
    }

    public function getNetmask(): ?string
    {
        return $this->netmask;
    }

    public function setNetmask(?string $netmask): self
    {
        $this->netmask = $netmask;

        return $this;
    }

    public function getNetmask0(): ?int
    {
        return $this->netmask_0;
    }

    public function setNetmask0(int $netmask_0): self
    {
        $this->netmask_0 = $netmask_0;

        return $this;
    }

    public function getNetmask1(): ?int
    {
        return $this->netmask_1;
    }

    public function setNetmask1(int $netmask_1): self
    {
        $this->netmask_1 = $netmask_1;

        return $this;
    }

    public function getNetmask2(): ?int
    {
        return $this->netmask_2;
    }

    public function setNetmask2(int $netmask_2): self
    {
        $this->netmask_2 = $netmask_2;

        return $this;
    }

    public function getNetmask3(): ?int
    {
        return $this->netmask_3;
    }

    public function setNetmask3(int $netmask_3): self
    {
        $this->netmask_3 = $netmask_3;

        return $this;
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(?string $gateway): self
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getGateway0(): ?int
    {
        return $this->gateway_0;
    }

    public function setGateway0(int $gateway_0): self
    {
        $this->gateway_0 = $gateway_0;

        return $this;
    }

    public function getGateway1(): ?int
    {
        return $this->gateway_1;
    }

    public function setGateway1(int $gateway_1): self
    {
        $this->gateway_1 = $gateway_1;

        return $this;
    }

    public function getGateway2(): ?int
    {
        return $this->gateway_2;
    }

    public function setGateway2(int $gateway_2): self
    {
        $this->gateway_2 = $gateway_2;

        return $this;
    }

    public function getGateway3(): ?int
    {
        return $this->gateway_3;
    }

    public function setGateway3(int $gateway_3): self
    {
        $this->gateway_3 = $gateway_3;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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
     * Get the value of ipaddressIpnetworks
     */ 
    public function getIpaddressIpnetworks()
    {
        return $this->ipaddressIpnetworks;
    }

    /**
     * Set the value of ipaddressIpnetworks
     *
     * @return  self
     */ 
    public function setIpaddressIpnetworks($ipaddressIpnetworks)
    {
        $this->ipaddressIpnetworks = $ipaddressIpnetworks;

        return $this;
    }
}
