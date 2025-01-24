<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_softwareversions')]
#[ORM\UniqueConstraint(name: "unicity", columns: ['itemtype', 'items_id', 'softwareversions_id'])]
#[ORM\Index(name: "items_id", columns: ['items_id'])]
#[ORM\Index(name: "itemtype", columns: ['itemtype'])]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "softwareversions_id", columns: ['softwareversions_id'])]
#[ORM\Index(name: "computers_info", columns: ['entities_id', 'is_template_item', 'is_deleted_item'])]
#[ORM\Index(name: "is_template", columns: ['is_template_item'])]
#[ORM\Index(name: "is_deleted", columns: ['is_deleted_item'])]
#[ORM\Index(name: "is_dynamic", columns: ['is_dynamic'])]
#[ORM\Index(name: "date_install", columns: ['date_install'])]
class ItemSoftwareVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'softwareversions_id', type: 'integer', options: ['default' => 0])]
    private $softwareversionsId;

    #[ORM\Column(name: 'is_deleted_item', type: 'boolean', options: ['default' => false])]
    private $isDeletedItem;

    #[ORM\Column(name: 'is_template_item', type: 'boolean', options: ['default' => false])]
    private $isTemplateItem;

    #[ORM\Column(name: 'entities_id', type: 'integer', options: ['default' => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => false])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => false])]
    private $isDynamic;

    #[ORM\Column(name: 'date_install', type: 'date', nullable: true)]
    private $dateInstall;

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

    public function getSoftwareversionsId(): ?int
    {
        return $this->softwareversionsId;
    }

    public function setSoftwareversionsId(int $softwareversionsId): self
    {
        $this->softwareversionsId = $softwareversionsId;

        return $this;
    }

    public function getIsDeletedItem(): ?bool
    {
        return $this->isDeletedItem;
    }

    public function setIsDeletedItem(bool $isDeletedItem): self
    {
        $this->isDeletedItem = $isDeletedItem;

        return $this;
    }

    public function getIsTemplateItem(): ?bool
    {
        return $this->isTemplateItem;
    }

    public function setIsTemplateItem(bool $isTemplateItem): self
    {
        $this->isTemplateItem = $isTemplateItem;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entitiesId;
    }

    public function setEntitiesId(int $entitiesId): self
    {
        $this->entitiesId = $entitiesId;

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

    public function getDateInstall(): ?DateTime
    {
        return $this->dateInstall;
    }

    public function setDateInstall(?DateTime $dateInstall): self
    {
        $this->dateInstall = $dateInstall;

        return $this;
    }
}
