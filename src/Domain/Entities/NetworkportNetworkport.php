<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_networkports_networkports')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['networkports_id_1', 'networkports_id_2'])]
#[ORM\Index(name: 'networkports_id_2', columns: ['networkports_id_2'])]
class NetworkportNetworkport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $networkports_id_1;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $networkports_id_2;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNetworkportsId1(): ?int
    {
        return $this->networkports_id_1;
    }

    public function setNetworkportsId1(?int $networkports_id_1): self
    {
        $this->networkports_id_1 = $networkports_id_1;

        return $this;
    }

    public function getNetworkportsId2(): ?int
    {
        return $this->networkports_id_2;
    }

    public function setNetworkportsId2(?int $networkports_id_2): self
    {
        $this->networkports_id_2 = $networkports_id_2;

        return $this;
    }

}
