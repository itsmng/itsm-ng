<?php


namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'networkports_id', columns: ['networkports_id'])]
#[ORM\Table(name: 'glpi_networkportaliases')]
#[ORM\Index(name: 'networkports_id_alias', columns: ['networkports_id_alias'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Networkportaliase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $networkports_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $networkports_id_alias;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

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

    public function getNetworkportsIdAlias(): ?int
    {
        return $this->networkports_id_alias;
    }

    public function setNetworkportsIdAlias(?int $networkports_id_alias): self
    {
        $this->networkports_id_alias = $networkports_id_alias;

        return $this;
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

}   



