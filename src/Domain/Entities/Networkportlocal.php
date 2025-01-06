<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name:'networkports_id', columns: ['networkports_id'])]
#[ORM\Table(name: 'glpi_networkportlocals')]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Networkportlocal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Networkport::class)]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?Networkport $networkport;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }


    /**
     * Get the value of networkport
     */
    public function getNetworkport()
    {
        return $this->networkport;
    }

    /**
     * Set the value of networkport
     *
     * @return  self
     */
    public function setNetworkport($networkport)
    {
        $this->networkport = $networkport;

        return $this;
    }
}
