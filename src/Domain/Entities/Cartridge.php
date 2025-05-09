<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_cartridges')]
#[ORM\Index(name: 'cartridgeitems_id', columns: ['cartridgeitems_id'])]
#[ORM\Index(name: 'printers_id', columns: ['printers_id'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Cartridge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;


    #[ORM\ManyToOne(targetEntity: CartridgeItem::class)]
    #[ORM\JoinColumn(name: 'cartridgeitems_id', referencedColumnName: 'id', nullable: true)]
    private ?CartridgeItem $cartridgeItem = null;


    #[ORM\ManyToOne(targetEntity: Printer::class)]
    #[ORM\JoinColumn(name: 'printers_id', referencedColumnName: 'id', nullable: true)]
    private ?Printer $printer = null;

    #[ORM\Column(name: 'date_in', type: 'date', nullable: true)]
    private $dateIn;

    #[ORM\Column(name: 'date_use', type: 'date', nullable: true)]
    private $dateUse = null;

    #[ORM\Column(name: 'date_out', type: 'date', nullable: true)]
    private $dateOut = null;

    #[ORM\Column(name: 'pages', type: 'integer', options: ['default' => 0])]
    private $pages = 0;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
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

    public function getDateUse(): ?\DateTimeInterface
    {
        return $this->dateUse;
    }

    public function setDateUse(\DateTimeInterface|string|null $dateUse): self
    {
        if (is_string($dateUse)) {
            $dateUse = new \DateTime($dateUse);
        }
        $this->dateUse = $dateUse;

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

    public function getPages(): ?int
    {
        return $this->pages;
    }

    public function setPages(int $pages): self
    {
        $this->pages = $pages;

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
     * Get the value of cartridgeItem
     */
    public function getCartridgeItem()
    {
        return $this->cartridgeItem;
    }

    /**
     * Set the value of cartridgeItem
     *
     * @return  self
     */
    public function setCartridgeItem($cartridgeItem)
    {
        $this->cartridgeItem = $cartridgeItem;

        return $this;
    }

    /**
     * Get the value of printer
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Set the value of printer
     *
     * @return  self
     */
    public function setPrinter($printer)
    {
        $this->printer = $printer;

        return $this;
    }
}
