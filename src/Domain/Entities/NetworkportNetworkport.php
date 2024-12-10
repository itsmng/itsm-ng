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

    #[ORM\ManyToOne(targetEntity: Networkport::class, inversedBy: 'networkportNetworkports1')]
    #[ORM\JoinColumn(name: 'networkports_id_1', referencedColumnName: 'id', nullable: true)]
    private ?Networkport $networkport1;

    #[ORM\ManyToOne(targetEntity: Networkport::class, inversedBy: 'networkportNetworkports2')]
    #[ORM\JoinColumn(name: 'networkports_id_2', referencedColumnName: 'id', nullable: true)]
    private ?Networkport $networkport2;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of networkport1
     */
    public function getNetworkport1()
    {
        return $this->networkport1;
    }

    /**
     * Set the value of networkport1
     *
     * @return  self
     */
    public function setNetworkport1($networkport1)
    {
        $this->networkport1 = $networkport1;

        return $this;
    }

    /**
     * Get the value of networkport2
     */
    public function getNetworkport2()
    {
        return $this->networkport2;
    }

    /**
     * Set the value of networkport2
     *
     * @return  self
     */
    public function setNetworkport2($networkport2)
    {
        $this->networkport2 = $networkport2;

        return $this;
    }
}
