<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_infocoms")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["itemtype", "items_id"])]
#[ORM\Index(name: "buy_date", columns: ["buy_date"])]
#[ORM\Index(name: "alert", columns: ["alert"])]
#[ORM\Index(name: "budgets_id", columns: ["budgets_id"])]
#[ORM\Index(name: "suppliers_id", columns: ["suppliers_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
#[ORM\Index(name: "businesscriticities_id", columns: ["businesscriticities_id"])]
class Infocom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;

    #[ORM\Column(type: "date", nullable: true)]
    private $buy_date;

    #[ORM\Column(type: "date", nullable: true)]
    private $use_date;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $warranty_duration;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $warranty_info;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $suppliers_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $order_number;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $delivery_number;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $immo_number;

    #[ORM\Column(type: "decimal", precision: 20, scale: 4, options: ["default" => "0.0000"])]
    private $value;

    #[ORM\Column(type: "decimal", precision: 20, scale: 4, options: ["default" => "0.0000"])]
    private $warranty_value;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $sink_time;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $sink_type;

    #[ORM\Column(type: "float", options: ["default" => 0.0])]
    private $sink_coeff;

    #[ORM\Column(type: "text", length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $bill;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $budgets_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $alert;

    #[ORM\Column(type: "date", nullable: true)]
    private $order_date;

    #[ORM\Column(type: "date", nullable: true)]
    private $delivery_date;

    #[ORM\Column(type: "date", nullable: true)]
    private $inventory_date;

    #[ORM\Column(type: "date", nullable: true)]
    private $warranty_date;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $decommission_date;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $businesscriticities_id;

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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getBuyDate(): ?\DateTimeInterface
    {
        return $this->buy_date;
    }

    public function setBuyDate(\DateTimeInterface $buy_date): self
    {
        $this->buy_date = $buy_date;

        return $this;
    }

    public function getUseDate(): ?\DateTimeInterface
    {
        return $this->use_date;
    }

    public function setUseDate(\DateTimeInterface $use_date): self
    {
        $this->use_date = $use_date;

        return $this;
    }

    public function getWarrantyDuration(): ?int
    {
        return $this->warranty_duration;
    }

    public function setWarrantyDuration(int $warranty_duration): self
    {
        $this->warranty_duration = $warranty_duration;

        return $this;
    }

    public function getWarrantyInfo(): ?string
    {
        return $this->warranty_info;
    }

    public function setWarrantyInfo(string $warranty_info): self
    {
        $this->warranty_info = $warranty_info;

        return $this;
    }

    public function getSuppliersId(): ?int
    {
        return $this->suppliers_id;
    }

    public function setSuppliersId(int $suppliers_id): self
    {
        $this->suppliers_id = $suppliers_id;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->order_number;
    }

    public function setOrderNumber(string $order_number): self
    {
        $this->order_number = $order_number;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getWarrantyValue(): ?float
    {
        return $this->warranty_value;
    }

    public function setWarrantyValue(float $warranty_value): self
    {
        $this->warranty_value = $warranty_value;

        return $this;
    }

    public function getSinkTime(): ?int
    {
        return $this->sink_time;
    }

    public function setSinkTime(int $sink_time): self
    {
        $this->sink_time = $sink_time;

        return $this;
    }

    public function getSinkType(): ?string
    {
        return $this->sink_type;
    }

    public function setSinkType(string $sink_type): self
    {
        $this->sink_type = $sink_type;

        return $this;
    }

    public function getSinkCoeff(): ?float
    {
        return $this->sink_coeff;
    }

    public function setSinkCoeff(float $sink_coeff): self
    {
        $this->sink_coeff = $sink_coeff;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getBill(): ?string
    {
        return $this->bill;
    }

    public function setBill(string $bill): self
    {
        $this->bill = $bill;

        return $this;
    }

    public function getBudgetsId(): ?int
    {
        return $this->budgets_id;
    }

    public function setBudgetsId(int $budgets_id): self
    {
        $this->budgets_id = $budgets_id;

        return $this;
    }

    public function getAlert(): ?int
    {
        return $this->alert;
    }

    public function setAlert(int $alert): self
    {
        $this->alert = $alert;

        return $this;
    }

    public function getOrderDate(): ?\DateTimeInterface
    {
        return $this->order_date;
    }

    public function setOrderDate(\DateTimeInterface $order_date): self
    {
        $this->order_date = $order_date;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->delivery_date;
    }

    public function setDeliveryDate(\DateTimeInterface $delivery_date): self
    {
        $this->delivery_date = $delivery_date;

        return $this;
    }

    public function getInventoryDate(): ?\DateTimeInterface
    {
        return $this->inventory_date;
    }

    public function setInventoryDate(\DateTimeInterface $inventory_date): self
    {
        $this->inventory_date = $inventory_date;

        return $this;
    }

    public function getWarrantyDate(): ?\DateTimeInterface
    {
        return $this->warranty_date;
    }

    public function setWarrantyDate(\DateTimeInterface $warranty_date): self
    {
        $this->warranty_date = $warranty_date;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDecommissionDate(): ?\DateTimeInterface
    {
        return $this->decommission_date;
    }

    public function setDecommissionDate(\DateTimeInterface $decommission_date): self
    {
        $this->decommission_date = $decommission_date;

        return $this;
    }

    public function getBusinesscriticitiesId(): ?int
    {
        return $this->businesscriticities_id;
    }

    public function setBusinesscriticitiesId(int $businesscriticities_id): self
    {
        $this->businesscriticities_id = $businesscriticities_id;

        return $this;
    }
}
