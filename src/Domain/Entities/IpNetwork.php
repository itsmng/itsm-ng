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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => false])]
    private $isRecursive = false;

    #[ORM\ManyToOne(targetEntity: IpNetwork::class)]
    #[ORM\JoinColumn(name: 'ipnetworks_id', referencedColumnName: 'id', nullable: true)]
    private ?IpNetwork $ipnetwork = null;

    #[ORM\Column(name: 'completename', type: "text", nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(name: 'level', type: "integer", options: ["default" => 0])]
    private $level = 0;

    #[ORM\Column(name: 'ancestors_cache', type: "text", nullable: true)]
    private $ancestorsCache;

    #[ORM\Column(name: 'sons_cache', type: "text", nullable: true)]
    private $sonsCache;

    #[ORM\Column(name: 'addressable', type: "boolean", options: ["default" => false])]
    private $addressable = false;

    #[ORM\Column(name: 'version', type: "smallint", nullable: true, options: ["unsigned" => true, "default" => 0])]
    private $version = 0;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'address', type: "string", length: 40, nullable: true)]
    private $address;

    #[ORM\Column(name: 'address_0', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address0 = 0;

    #[ORM\Column(name: 'address_1', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address1 = 0;

    #[ORM\Column(name: 'address_2', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address2 = 0;

    #[ORM\Column(name: 'address_3', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $address3 = 0;

    #[ORM\Column(name: 'netmask', type: "string", length: 40, nullable: true)]
    private $netmask;

    #[ORM\Column(name: 'netmask_0', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask0 = 0;

    #[ORM\Column(name: 'netmask_1', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask1 = 0;

    #[ORM\Column(name: 'netmask_2', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask2 = 0;

    #[ORM\Column(name: 'netmask_3', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $netmask3 = 0;

    #[ORM\Column(name: 'gateway', type: "string", length: 40, nullable: true)]
    private $gateway;

    #[ORM\Column(name: 'gateway_0', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway0 = 0;

    #[ORM\Column(name: 'gateway_1', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway1 = 0;

    #[ORM\Column(name: 'gateway_2', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway2 = 0;

    #[ORM\Column(name: 'gateway_3', type: "integer", options: ["unsigned" => true, "default" => 0])]
    private $gateway3 = 0;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'ipnetwork', targetEntity: IpAddressIpNetwork::class)]
    private Collection $ipaddressIpnetworks;

    #[ORM\OneToMany(mappedBy: 'ipnetwork', targetEntity: IpNetworkVlan::class)]
    private Collection $ipnetworkVlans;


    public function __construct()
    {
        $this->dateMod = new \DateTime();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->ancestorsCache;
    }

    public function setAncestorsCache(?string $ancestorsCache): self
    {
        $this->ancestorsCache = $ancestorsCache;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sonsCache;
    }

    public function setSonsCache(?string $sonsCache): self
    {
        $this->sonsCache = $sonsCache;

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
        return $this->address0;
    }

    public function setAddress0(int $address0): self
    {
        $this->address0 = $address0;

        return $this;
    }

    public function getAddress1(): ?int
    {
        return $this->address1;
    }

    public function setAddress1(int $address1): self
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?int
    {
        return $this->address2;
    }

    public function setAddress2(int $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?int
    {
        return $this->address3;
    }

    public function setAddress3(int $address3): self
    {
        $this->address3 = $address3;

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
        return $this->netmask0;
    }

    public function setNetmask0(int $netmask0): self
    {
        $this->netmask0 = $netmask0;

        return $this;
    }

    public function getNetmask1(): ?int
    {
        return $this->netmask1;
    }

    public function setNetmask1(int $netmask1): self
    {
        $this->netmask1 = $netmask1;

        return $this;
    }

    public function getNetmask2(): ?int
    {
        return $this->netmask2;
    }

    public function setNetmask2(int $netmask2): self
    {
        $this->netmask2 = $netmask2;

        return $this;
    }

    public function getNetmask3(): ?int
    {
        return $this->netmask3;
    }

    public function setNetmask3(int $netmask3): self
    {
        $this->netmask3 = $netmask3;

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
        return $this->gateway0;
    }

    public function setGateway0(int $gateway0): self
    {
        $this->gateway0 = $gateway0;

        return $this;
    }

    public function getGateway1(): ?int
    {
        return $this->gateway1;
    }

    public function setGateway1(int $gateway1): self
    {
        $this->gateway1 = $gateway1;

        return $this;
    }

    public function getGateway2(): ?int
    {
        return $this->gateway2;
    }

    public function setGateway2(int $gateway2): self
    {
        $this->gateway2 = $gateway2;

        return $this;
    }

    public function getGateway3(): ?int
    {
        return $this->gateway3;
    }

    public function setGateway3(int $gateway3): self
    {
        $this->gateway3 = $gateway3;

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
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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

    /**
     * Get the value of entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

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
     * Get the value of ipnetworkVlans
     */
    public function getIpnetworkVlans()
    {
        return $this->ipnetworkVlans;
    }

    /**
     * Set the value of ipnetworkVlans
     *
     * @return  self
     */
    public function setIpnetworkVlans($ipnetworkVlans)
    {
        $this->ipnetworkVlans = $ipnetworkVlans;

        return $this;
    }
}
