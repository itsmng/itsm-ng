<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_problems")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["problems_id", "itemtype", "items_id"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
class ItemProblem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Problem::class)]
    #[ORM\JoinColumn(name: 'problems_id', referencedColumnName: 'id', nullable: true)]
    private ?Problem $problem = null;

    #[ORM\Column(name: 'itemtype', type: "string", length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    /**
     * Get the value of problem
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * Set the value of problem
     *
     * @return  self
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;

        return $this;
    }
}
