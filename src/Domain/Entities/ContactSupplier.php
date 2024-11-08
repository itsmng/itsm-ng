<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_contacts_suppliers')]
#[ORM\UniqueConstraint(name: 'suppliers_id_contacts_id', columns: ['suppliers_id', 'contacts_id'])]
#[ORM\Index(name: 'contacts_id', columns: ['contacts_id'])]

class ContactSupplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $suppliers_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $contacts_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSuppliersId(): ?int
    {
        return $this->suppliers_id;
    }

    public function setSuppliersId(int $suppliers_id): self
    {
        $this->suppliers_id = $suppliers_id;

        return $this;
    }

    public function getContactsId(): ?int
    {
        return $this->contacts_id;
    }

    public function setContactsId(int $contacts_id): self
    {
        $this->contacts_id = $contacts_id;

        return $this;
    }
}