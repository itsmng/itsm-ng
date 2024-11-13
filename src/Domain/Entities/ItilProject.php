<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

//Table => glpi_itils_projects
//
//Select data Show structure Alter table New item
//Column	Type	Comment
//id	int(11) Auto Increment
//itemtype	varchar(100) []
//items_id	int(11) [0]
//projects_id	int(11) [0]
//Indexes
//PRIMARY	id
//UNIQUE	itemtype, items_id, projects_id
//INDEX	projects_id
//

#[ORM\Entity]
#[ORM\Table(name: "glpi_itils_projects")]
#[ORM\UniqueConstraint(columns: ["itemtype", "items_id", "projects_id"])]
#[ORM\Index(columns: ["projects_id"])]
class ItilProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 100, options: ["default" => ""])]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $items_id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $projects_id;

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
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }

    public function getProjectsId(): ?int
    {
        return $this->projects_id;
    }

    public function setProjectsId(int $projects_id): self
    {
        $this->projects_id = $projects_id;

        return $this;
    }
}
