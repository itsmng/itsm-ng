<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_contacts_suppliers')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['suppliers_id', 'contacts_id'])]
#[ORM\Index(name: 'contacts_id', columns: ['contacts_id'])]

class ContactSupplier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

   
    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'contactSuppliers')]
    #[ORM\JoinColumn(name: 'suppliers_id', referencedColumnName: 'id', nullable: true)]
    private ?Supplier $supplier;

   
    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'contactSuppliers')]
    #[ORM\JoinColumn(name: 'contacts_id', referencedColumnName: 'id', nullable: true)]
    private ?Contact $contact;

    public function getId(): ?int
    {
        return $this->id;
    }

    
    /**
     * Get the value of supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set the value of supplier
     *
     * @return  self
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get the value of contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set the value of contact
     *
     * @return  self
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }
}
