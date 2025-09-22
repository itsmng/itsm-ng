<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_certificates_items')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['certificates_id', 'itemtype', 'items_id'])]
#[ORM\Index(name: 'device', columns: ['items_id', 'itemtype'])]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]

class CertificateItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Certificate::class)]
    #[ORM\JoinColumn(name: 'certificates_id', referencedColumnName: 'id', nullable: true)]
    private ?Certificate $certificate = null;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0, 'comment' => 'RELATION to various tables, according to itemtype (id)'])]
    private $items_id = 0;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100, options: ['comment' => 'see .class.php file'])]
    private $itemtype;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    /**
     * Get the value of certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * Set the value of certificate
     *
     * @return  self
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }
}
