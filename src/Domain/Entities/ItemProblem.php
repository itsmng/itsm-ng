<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_items_problems")]
#[ORM\UniqueConstraint(columns: ["problems_id", "itemtype", "items_id"])]
#[ORM\Index(columns: ["itemtype", "items_id"])]
class ItemProblem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $problems_id;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProblemsId(): ?int
    {
        return $this->problems_id;
    }

    public function setProblemsId(int $problems_id): self
    {
        $this->problems_id = $problems_id;

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

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }
}
