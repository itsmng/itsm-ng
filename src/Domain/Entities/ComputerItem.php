<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_computers_items')]
#[ORM\Index(name: 'computers_id', columns: ['computers_id'])]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'is_dynamic', columns: ['is_dynamic'])]

class ComputerItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0, 'comment' => 'RELATION to various table, according to itemtype (ID)'])]
    private $items_id = 0;

    #[ORM\ManyToOne(targetEntity: Computer::class)]
    #[ORM\JoinColumn(name: 'computers_id', referencedColumnName: 'id', nullable: true)]
    private ?Computer $computer = null;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => 0])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => 0])]
    private $isDynamic;

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

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

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

    /**
     * Get the value of computer
     */
    public function getComputer()
    {
        return $this->computer;
    }

    /**
     * Set the value of computer
     *
     * @return  self
     */
    public function setComputer($computer)
    {
        $this->computer = $computer;

        return $this;
    }
}
