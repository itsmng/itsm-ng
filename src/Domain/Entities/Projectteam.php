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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'projects_id', referencedColumnName: 'id', nullable: true)]
    private ?Project $project = null;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

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
        return $this->itemsId;
    }

    public function setItemsId(?int $itemsId): self
    {
        $this->itemsId = $itemsId;

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
