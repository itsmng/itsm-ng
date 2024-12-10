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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $logical_number;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $instantiation_type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mac;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_dynamic;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'networkport1', targetEntity: NetworkportNetworkport::class)]
    private Collection $networkportNetworkports1;

    #[ORM\OneToMany(mappedBy: 'networkport2', targetEntity: NetworkportNetworkport::class)]
    private Collection $networkportNetworkports2;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->items_id = $itemsId;

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

    public function getIs_recursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIs_recursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getLogical_number(): ?int
    {
        return $this->logical_number;
    }

    public function setLogical_number(int $logical_number): self
    {
        $this->logical_number = $logical_number;

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

    public function getInstantiation_type(): ?string
    {
        return $this->instantiation_type;
    }

    public function setInstantiation_type(string $instantiation_type): self
    {
        $this->instantiation_type = $instantiation_type;

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
        return $this->is_deleted;
    }

    public function setIsDeleted(int $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?int
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(int $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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
}
