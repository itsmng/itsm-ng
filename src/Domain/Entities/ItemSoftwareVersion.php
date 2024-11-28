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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $softwareversions_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_deleted_item;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_template_item;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_dynamic;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date_install;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

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
        return $this->softwareversions_id;
    }

    public function setSoftwareversionsId(int $softwareversions_id): self
    {
        $this->softwareversions_id = $softwareversions_id;

        return $this;
    }

    public function getIsDeletedItem(): ?bool
    {
        return $this->is_deleted_item;
    }

    public function setIsDeletedItem(bool $is_deleted_item): self
    {
        $this->is_deleted_item = $is_deleted_item;

        return $this;
    }

    public function getIsTemplateItem(): ?bool
    {
        return $this->is_template_item;
    }

    public function setIsTemplateItem(bool $is_template_item): self
    {
        $this->is_template_item = $is_template_item;

        return $this;
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

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getDateInstall(): ?DateTime
    {
        return $this->date_install;
    }

    public function setDateInstall(?DateTime $date_install): self
    {
        $this->date_install = $date_install;

        return $this;
    }
}
