<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_certificates_items')]
#[ORM\UniqueConstraint(name: 'certificates_id_itemtype_items_id', columns: ['certificates_id', 'itemtype', 'items_id'])]
#[ORM\Index(name: 'items_id_itemtype', columns: ['items_id', 'itemtype'])]
#[ORM\Index(name: 'itemtype_items_id', columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]

class CertificateItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', name: 'certificates_id', nullable: false)]
    private $certificates_id;

    #[ORM\ManyToOne(targetEntity: Certificate::class)]
    #[ORM\JoinColumn(name: 'certificates_id', referencedColumnName: 'id', nullable: false)]
    private ?Certificate $certificate;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'RELATION to various tables, according to itemtype (id)'])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100, options: ['comment' => 'see .class.php file'])]
    private $itemtype;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_mod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCertificatesId(): ?int
    {
        return $this->certificates_id;
    }

    public function setCertificatesId(int $certificates_id): self
    {
        $this->certificates_id = $certificates_id;

        return $this;
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
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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
