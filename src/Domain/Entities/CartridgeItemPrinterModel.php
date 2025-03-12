<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_cartridgeitems_printermodels")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["printermodels_id", "cartridgeitems_id"])]
#[ORM\Index(name: "cartridgeitems_id", columns: ["cartridgeitems_id"])]
class CartridgeItemPrinterModel
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;


    #[ORM\ManyToOne(targetEntity: CartridgeItem::class, inversedBy: 'cartridgeItemPrinterModels')]
    #[ORM\JoinColumn(name: 'cartridgeitems_id', referencedColumnName: 'id', nullable: true)]
    private ?CartridgeItem $cartridgeItem = null;


    #[ORM\ManyToOne(targetEntity: PrinterModel::class, inversedBy: 'cartridgeItemPrinterModels')]
    #[ORM\JoinColumn(name: 'printermodels_id', referencedColumnName: 'id', nullable: true)]
    private ?PrinterModel $printermodel = null;


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
    public function getPrinterModel()
    {
        return $this->printermodel;
    }

    /**
     * Set the value of printermodel
     *
     * @return  self
     */
    public function setPrinterModel($printermodel)
    {
        $this->printermodel = $printermodel;

        return $this;
    }
}
