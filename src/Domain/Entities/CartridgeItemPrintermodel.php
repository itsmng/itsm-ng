<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_cartridgeitems_printermodels")]
#[ORM\UniqueConstraint(name: "printermodels_id_cartridgeitems_id", columns: ["printermodels_id", "cartridgeitems_id"])]
#[ORM\Index(name: "cartridgeitems_id", columns: ["cartridgeitems_id"])]
class CartridgeItemPrintermodel
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: "integer", name: "cartridgeitems_id", options: ["default" => 0])]
    private $cartridgeitems_id;

    #[ORM\ManyToOne(targetEntity: CartridgeItem::class, inversedBy: 'cartridgeItemPrintermodels')]
    #[ORM\JoinColumn(name: 'cartridgeitems_id', referencedColumnName: 'id', nullable: false)]
    private ?CartridgeItem $cartridgeItem;

    #[ORM\Column(type: "integer", name: "printermodels_id", options: ["default" => 0])]
    private $printermodels_id;

    #[ORM\ManyToOne(targetEntity: Printermodel::class, inversedBy: 'cartridgeItemPrintermodels')]
    #[ORM\JoinColumn(name: 'printermodels_id', referencedColumnName: 'id', nullable: false)]
    private ?Printermodel $printermodel;
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCartridgeitemsId(): ?int
    {
        return $this->cartridgeitems_id;
    }

    public function setCartridgeitemsId(int $cartridgeitems_id): self
    {
        $this->cartridgeitems_id = $cartridgeitems_id;

        return $this;
    }

    public function getPrintermodelsId(): ?int
    {
        return $this->printermodels_id;
    }

    public function setPrintermodelsId(int $printermodels_id): self
    {
        $this->printermodels_id = $printermodels_id;

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
