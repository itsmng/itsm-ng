<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_networkportaliases')]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Index(name: 'networkports_id_alias', columns: ['networkports_id_alias'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class NetworkPortAlias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: NetworkPort::class)]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?NetworkPort $networkport = null;

    #[ORM\ManyToOne(targetEntity: NetworkPort::class)]
    #[ORM\JoinColumn(name: 'networkports_id_alias', referencedColumnName: 'id', nullable: true)]
    private ?NetworkPort $networkportAlias = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime')]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime')]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }


    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

        return $this;
    }


    /**
     * Get the value of networkport
     */
    public function getNetworkPort()
    {
        return $this->networkport;
    }

    /**
     * Set the value of networkport
     *
     * @return  self
     */
    public function setNetworkPort($networkport)
    {
        $this->networkport = $networkport;

        return $this;
    }

    /**
     * Get the value of networkportAlias
     */
    public function getNetworkPortAlias()
    {
        return $this->networkportAlias;
    }

    /**
     * Set the value of networkportAlias
     *
     * @return  self
     */
    public function setNetworkPortAlias($networkportAlias)
    {
        $this->networkportAlias = $networkportAlias;

        return $this;
    }
}
