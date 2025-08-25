<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Table(name: 'glpi_networkportaggregates')]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class NetworkPortAggregate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: NetworkPort::class)]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?NetworkPort $networkport = null;

    #[ORM\ManyToOne(targetEntity: NetworkPort::class)]
    #[ORM\JoinColumn(name: 'networkports_id_list', referencedColumnName: 'id', nullable: true, options: ['comment' => 'array of associated networkports_id'])]
    private ?NetworkPort $networkportList = null;

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
     * Get the value of networkportList
     */
    public function getNetworkPortList()
    {
        return $this->networkportList;
    }

    /**
     * Set the value of networkportList
     *
     * @return  self
     */
    public function setNetworkPortList($networkportList)
    {
        $this->networkportList = $networkportList;

        return $this;
    }
}
