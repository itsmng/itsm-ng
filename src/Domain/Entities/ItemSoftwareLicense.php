<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_softwarelicenses")]
#[ORM\Index(name: "items_id", columns: ["items_id"])]
#[ORM\Index(name: "itemtype", columns: ["itemtype"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "softwarelicenses_id", columns: ["softwarelicenses_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
class ItemSoftwareLicense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: "string", length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: SoftwareLicense::class)]
    #[ORM\JoinColumn(name: 'softwarelicenses_id', referencedColumnName: 'id', nullable: true)]
    private ?SoftwareLicense $softwarelicense = null;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => false])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => false])]
    private $isDynamic;

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

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    /**
     * Get the value of softwarelicense
     */
    public function getSoftwarelicense()
    {
        return $this->softwarelicense;
    }

    /**
     * Set the value of softwarelicense
     *
     * @return  self
     */
    public function setSoftwarelicense($softwarelicense)
    {
        $this->softwarelicense = $softwarelicense;

        return $this;
    }
}
