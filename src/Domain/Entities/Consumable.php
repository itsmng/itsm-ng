<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_consumables')]
#[ORM\Index(name: 'date_in', columns: ['date_in'])]
#[ORM\Index(name: 'date_out', columns: ['date_out'])]
#[ORM\Index(name: 'consumableitems_id', columns: ['consumableitems_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Consumable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\ManyToOne(targetEntity: ConsumableItem::class)]
    #[ORM\JoinColumn(name: 'consumableitems_id', referencedColumnName: 'id', nullable: true)]
    private ?ConsumableItem $consumableitem = null;

    #[ORM\Column(name: 'date_in', type: 'date', nullable: true)]
    private $dateIn;

    #[ORM\Column(name: 'date_out', type: 'date', nullable: true)]
    private $dateOut;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $items_id = 0;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getDateIn(): ?\DateTimeInterface
    {
        return $this->dateIn;
    }

    public function setDateIn(\DateTimeInterface|string|null $dateIn): self
    {
        if (is_string($dateIn)) {
            $dateIn = new \DateTime($dateIn);
        }
        $this->dateIn = $dateIn;

        return $this;
    }

    public function getDateOut(): ?\DateTimeInterface
    {
        return $this->dateOut;
    }

    public function setDateOut(\DateTimeInterface|string|null $dateOut): self
    {
        if (is_string($dateOut)) {
            $dateOut = new \DateTime($dateOut);
        }
        $this->dateOut = $dateOut;

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

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

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
     * Get the value of consumableitem
     */
    public function getConsumableitem()
    {
        return $this->consumableitem;
    }

    /**
     * Set the value of consumableitem
     *
     * @return  self
     */
    public function setConsumableitem($consumableitem)
    {
        $this->consumableitem = $consumableitem;

        return $this;
    }
}
