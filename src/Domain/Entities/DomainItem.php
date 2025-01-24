<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_domains_items')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['domains_id', 'itemtype', 'items_id'])]
#[ORM\Index(name: 'domains_id', columns: ['domains_id'])]
#[ORM\Index(name: 'domainrelations_id', columns: ['domainrelations_id'])]
#[ORM\Index(name: 'fk_device', columns: ['items_id', 'itemtype'])]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id'])]
class DomainItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Domain::class, inversedBy: 'domainItems')]
    #[ORM\JoinColumn(name: 'domains_id', referencedColumnName: 'id', nullable: true)]
    private ?Domain $domain = null;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'domainrelations_id', type: 'integer', options: ['default' => 0])]
    private $domainrelationsId;

    #[ORM\ManyToOne(targetEntity: DomainRelation::class)]
    #[ORM\JoinColumn(name: 'domainrelations_id', referencedColumnName: 'id', nullable: true)]
    private ?DomainRelation $domainRelation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(?int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getDomainrelationsId(): ?int
    {
        return $this->domainrelationsId;
    }

    public function setDomainrelationsId(?int $domainrelationsId): self
    {
        $this->domainrelationsId = $domainrelationsId;

        return $this;
    }


    /**
     * Get the value of domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the value of domain
     *
     * @return  self
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get the value of domainRelation
     */
    public function getDomainRelation()
    {
        return $this->domainRelation;
    }

    /**
     * Set the value of domainRelation
     *
     * @return  self
     */
    public function setDomainRelation($domainRelation)
    {
        $this->domainRelation = $domainRelation;

        return $this;
    }
}
