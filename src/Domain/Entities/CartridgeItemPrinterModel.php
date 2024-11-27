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
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $cartridgeitems_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $printermodels_id;

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
}
