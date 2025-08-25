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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(name: 'itemtype', type: "string", length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'buy_date', type: "date", nullable: true)]
    private $buyDate;

    #[ORM\Column(name: 'use_date', type: "date", nullable: true)]
    private $useDate;

    #[ORM\Column(name: 'warranty_duration', type: "integer", options: ["default" => 0])]
    private $warrantyDuration;

    #[ORM\Column(name: 'warranty_info', type: "string", length: 255, nullable: true)]
    private $warrantyInfo;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(name: 'suppliers_id', referencedColumnName: 'id', nullable: true)]
    private ?Supplier $supplier = null;

    #[ORM\Column(name: 'order_number', type: "string", length: 255, nullable: true)]
    private $orderNumber;

    #[ORM\Column(name: 'delivery_number', type: "string", length: 255, nullable: true)]
    private $deliveryNumber;

    #[ORM\Column(name: 'immo_number', type: "string", length: 255, nullable: true)]
    private $immoNumber;

    #[ORM\Column(name: 'value', type: "decimal", precision: 20, scale: 4, options: ["default" => "0.0000"])]
    private $value;

    #[ORM\Column(name: 'warranty_value', type: "decimal", precision: 20, scale: 4, options: ["default" => "0.0000"])]
    private $warrantyValue;

    #[ORM\Column(name: 'sink_time', type: "integer", options: ["default" => 0])]
    private $sinkTime;

    #[ORM\Column(name: 'sink_type', type: "integer", options: ["default" => 0])]
    private $sinkType;

    #[ORM\Column(name: 'sink_coeff', type: "float", options: ["default" => 0.0])]
    private $sinkCoeff;

    #[ORM\Column(name: 'comment', type: "text", length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'bill', type: "string", length: 255, nullable: true)]
    private $bill;

    #[ORM\ManyToOne(targetEntity: Budget::class)]
    #[ORM\JoinColumn(name: 'budgets_id', referencedColumnName: 'id', nullable: true)]
    private ?Budget $budget = null;

    #[ORM\Column(name: 'alert', type: "integer", options: ["default" => 0])]
    private $alert;

    #[ORM\Column(name: 'order_date', type: "date", nullable: true)]
    private $orderDate;

    #[ORM\Column(name: 'delivery_date', type: "date", nullable: true)]
    private $deliveryDate;

    #[ORM\Column(name: 'inventory_date', type: "date", nullable: true)]
    private $inventoryDate;

    #[ORM\Column(name: 'warranty_date', type: "date", nullable: true)]
    private $warrantyDate;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'decommission_date', type: "datetime", nullable: true)]
    private $decommissionDate;

    #[ORM\ManyToOne(targetEntity: BusinessCriticity::class)]
    #[ORM\JoinColumn(name: 'businesscriticities_id', referencedColumnName: 'id', nullable: true)]
    private ?BusinessCriticity $businesscriticity = null;

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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getBuyDate(): ?\DateTimeInterface
    {
        return $this->buyDate;
    }

    public function setBuyDate(\DateTimeInterface | null $buyDate): self
    {
        $this->buyDate = $buyDate;

        return $this;
    }

    public function getUseDate(): ?\DateTimeInterface
    {
        return $this->useDate;
    }

    public function setUseDate(\DateTimeInterface | null $useDate): self
    {
        $this->useDate = $useDate;

        return $this;
    }

    public function getWarrantyDuration(): ?int
    {
        return $this->warrantyDuration;
    }

    public function setWarrantyDuration(int $warrantyDuration): self
    {
        $this->warrantyDuration = $warrantyDuration;

        return $this;
    }

    public function getWarrantyInfo(): ?string
    {
        return $this->warrantyInfo;
    }

    public function setWarrantyInfo(string | null $warrantyInfo): self
    {
        $this->warrantyInfo = $warrantyInfo;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string | null $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

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
        return $this->warrantyValue;
    }

    public function setWarrantyValue(float $warrantyValue): self
    {
        $this->warrantyValue = $warrantyValue;

        return $this;
    }

    public function getSinkTime(): ?int
    {
        return $this->sinkTime;
    }

    public function setSinkTime(int $sinkTime): self
    {
        $this->sinkTime = $sinkTime;

        return $this;
    }

    public function getSinkType(): ?string
    {
        return $this->sinkType;
    }

    public function setSinkType(string $sinkType): self
    {
        $this->sinkType = $sinkType;

        return $this;
    }

    public function getSinkCoeff(): ?float
    {
        return $this->sinkCoeff;
    }

    public function setSinkCoeff(float $sinkCoeff): self
    {
        $this->sinkCoeff = $sinkCoeff;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string | null $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getBill(): ?string
    {
        return $this->bill;
    }

    public function setBill(string | null $bill): self
    {
        $this->bill = $bill;

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
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeInterface | null $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(\DateTimeInterface | null $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getInventoryDate(): ?\DateTimeInterface
    {
        return $this->inventoryDate;
    }

    public function setInventoryDate(\DateTimeInterface | null $inventoryDate): self
    {
        $this->inventoryDate = $inventoryDate;

        return $this;
    }

    public function getWarrantyDate(): ?\DateTimeInterface
    {
        return $this->warrantyDate;
    }

    public function setWarrantyDate(\DateTimeInterface | null $warrantyDate): self
    {
        $this->warrantyDate = $warrantyDate;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDecommissionDate(): ?\DateTimeInterface
    {
        return $this->decommissionDate;
    }

    public function setDecommissionDate(\DateTimeInterface | null $decommissionDate): self
    {
        $this->decommissionDate = $decommissionDate;

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
     * Get the value of delivery_number
     */
    public function getDeliveryNumber()
    {
        return $this->deliveryNumber;
    }

    /**
     * Set the value of delivery_number
     *
     * @return  self
     */
    public function setDeliveryNumber($deliveryNumber)
    {
        $this->deliveryNumber = $deliveryNumber;

        return $this;
    }

    /**
     * Get the value of immo_number
     */
    public function getImmoNumber()
    {
        return $this->immoNumber;
    }

    /**
     * Set the value of immo_number
     *
     * @return  self
     */
    public function setImmoNumber($immoNumber)
    {
        $this->immoNumber = $immoNumber;

        return $this;
    }

    /**
     * Get the value of supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set the value of supplier
     *
     * @return  self
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get the value of budget
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set the value of budget
     *
     * @return  self
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get the value of businesscriticity
     */
    public function getBusinesscriticity()
    {
        return $this->businesscriticity;
    }

    /**
     * Set the value of businesscriticity
     *
     * @return  self
     */
    public function setBusinesscriticity($businesscriticity)
    {
        $this->businesscriticity = $businesscriticity;

        return $this;
    }
}
