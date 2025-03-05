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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Networkport::class)]
    #[ORM\JoinColumn(name: 'networkports_id', referencedColumnName: 'id', nullable: true)]
    private ?Networkport $networkport = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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
