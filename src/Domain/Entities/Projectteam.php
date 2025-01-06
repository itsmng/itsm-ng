<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projectteams')]
#[ORM\UniqueConstraint(name: "unicity", columns: ['projects_id', 'itemtype', 'items_id'])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
class Projectteam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'projects_id', referencedColumnName: 'id', nullable: true)]
    private ?Project $project;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(?int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }


    /**
     * Get the value of project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set the value of project
     *
     * @return  self
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }
}
