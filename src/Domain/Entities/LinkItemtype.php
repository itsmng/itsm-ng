<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_links_itemtypes')]
#[ORM\UniqueConstraint(columns: ['itemtype', 'links_id'])]
#[ORM\Index(columns: ['links_id'])]
class LinkItemtype
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $links_id;

    #[ORM\Column(type: 'string', length: 100, options: ['default' => ''])]
    private $itemtype;

    public function getId(): int
    {
        return $this->id;
    }

    public function getLinksId(): int
    {
        return $this->links_id;
    }

    public function setLinksId(int $links_id): self
    {
        $this->links_id = $links_id;

        return $this;
    }

    public function getItemtype(): string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }
}
