<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_projecttaskteams')]
#[ORM\UniqueConstraint(name: "unicity", columns: ['projecttasks_id', 'itemtype', 'items_id'])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id"])]
class Projecttaskteam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Projecttask::class)]
    #[ORM\JoinColumn(name: 'projecttasks_id', referencedColumnName: 'id', nullable: true)]
    private ?Projecttask $projecttask;

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
     * Get the value of projecttask
     */
    public function getProjecttask()
    {
        return $this->projecttask;
    }

    /**
     * Set the value of projecttask
     *
     * @return  self
     */
    public function setProjecttask($projecttask)
    {
        $this->projecttask = $projecttask;

        return $this;
    }
}
