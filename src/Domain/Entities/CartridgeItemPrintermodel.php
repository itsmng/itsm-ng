<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_cartridgeitems_printermodels")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["printermodels_id", "cartridgeitems_id"])]
#[ORM\Index(name: "cartridgeitems_id", columns: ["cartridgeitems_id"])]
class CartridgeItemPrintermodel
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private $id;

    
    #[ORM\ManyToOne(targetEntity: CartridgeItem::class, inversedBy: 'cartridgeItemPrintermodels')]
    #[ORM\JoinColumn(name: 'cartridgeitems_id', referencedColumnName: 'id', nullable: true)]
    private ?CartridgeItem $cartridgeItem;

    
    #[ORM\ManyToOne(targetEntity: Printermodel::class, inversedBy: 'cartridgeItemPrintermodels')]
    #[ORM\JoinColumn(name: 'printermodels_id', referencedColumnName: 'id', nullable: true)]
    private ?Printermodel $printermodel;


    public function getId(): ?int
    {
        return $this->id;
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
     * Get the value of printermodel
     */
    public function getPrintermodel()
    {
        return $this->printermodel;
    }

    /**
     * Set the value of printermodel
     *
     * @return  self
     */
    public function setPrintermodel($printermodel)
    {
        $this->printermodel = $printermodel;

        return $this;
    }
}
