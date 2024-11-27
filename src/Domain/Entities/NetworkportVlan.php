<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkports_vlans')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['networkports_id', 'vlans_id'])]
#[ORM\Index(name: 'vlans_id', columns: ['vlans_id'])]
class NetworkportVlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $networkports_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $vlans_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $tagged;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNetworkportsId(): ?int
    {
        return $this->networkports_id;
    }

    public function setNetworkportsId(?int $networkports_id): self
    {
        $this->networkports_id = $networkports_id;

        return $this;
    }

    public function getVlansId(): ?int
    {
        return $this->vlans_id;
    }

    public function setVlansId(?int $vlans_id): self
    {
        $this->vlans_id = $vlans_id;

        return $this;
    }

    public function getTagged(): ?bool
    {
        return $this->tagged;
    }

    public function setTagged(?bool $tagged): self
    {
        $this->tagged = $tagged;

        return $this;
    }

}
