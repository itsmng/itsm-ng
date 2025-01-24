<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkports')]
#[ORM\Index(name: 'on_device', columns: ['items_id', 'itemtype'])]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'is_recursive', columns: ['is_recursive'])]
#[ORM\Index(name: 'mac', columns: ['mac'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'is_dynamic', columns: ['is_dynamic'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Networkport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'logical_number', type: 'integer', options: ['default' => 0])]
    private $logicalNumber;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'instantiation_type', type: 'string', length: 255, nullable: true)]
    private $instantiationType;

    #[ORM\Column(name: 'mac', type: 'string', length: 255, nullable: true)]
    private $mac;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => 0])]
    private $isDynamic;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'networkport1', targetEntity: NetworkportNetworkport::class)]
    private Collection $networkportNetworkports1;

    #[ORM\OneToMany(mappedBy: 'networkport2', targetEntity: NetworkportNetworkport::class)]
    private Collection $networkportNetworkports2;

    #[ORM\OneToMany(mappedBy: 'networkport', targetEntity: NetworkportVlan::class)]
    private Collection $networkportVlans;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
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

    public function getLogicalNumber(): ?int
    {
        return $this->logicalNumber;
    }

    public function setLogicalNumber(int $logicalNumber): self
    {
        $this->logicalNumber = $logicalNumber;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInstantiationType(): ?string
    {
        return $this->instantiationType;
    }

    public function setInstantiationType(string $instantiationType): self
    {
        $this->instantiationType = $instantiationType;

        return $this;
    }

    public function getMac(): ?string
    {
        return $this->mac;
    }

    public function setMac(string $mac): self
    {
        $this->mac = $mac;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsDeleted(): ?int
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(int $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?int
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(int $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

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
     * Get the value of networkportNetworkports1
     */
    public function getNetworkportNetworkports1()
    {
        return $this->networkportNetworkports1;
    }

    /**
     * Set the value of networkportNetworkports1
     *
     * @return  self
     */
    public function setNetworkportNetworkports1($networkportNetworkports1)
    {
        $this->networkportNetworkports1 = $networkportNetworkports1;

        return $this;
    }

    /**
     * Get the value of networkportNetworkports2
     */
    public function getNetworkportNetworkports2()
    {
        return $this->networkportNetworkports2;
    }

    /**
     * Set the value of networkportNetworkports2
     *
     * @return  self
     */
    public function setNetworkportNetworkports2($networkportNetworkports2)
    {
        $this->networkportNetworkports2 = $networkportNetworkports2;

        return $this;
    }

    /**
     * Get the value of networkportVlans
     */
    public function getNetworkportVlans()
    {
        return $this->networkportVlans;
    }

    /**
     * Set the value of networkportVlans
     *
     * @return  self
     */
    public function setNetworkportVlans($networkportVlans)
    {
        $this->networkportVlans = $networkportVlans;

        return $this;
    }
}
