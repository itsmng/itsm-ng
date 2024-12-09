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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Domain::class, inversedBy: 'domainItems')]
    #[ORM\JoinColumn(name: 'domains_id', referencedColumnName: 'id', nullable: true)]
    private ?Domain $domain;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $domainrelations_id;

    #[ORM\ManyToOne(targetEntity: DomainRelation::class)]
    #[ORM\JoinColumn(name: 'domainrelations_id', referencedColumnName: 'id', nullable: true)]
    private ?DomainRelation $domainRelation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(?int $items_id): self
    {
        $this->items_id = $items_id;

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
        return $this->domainrelations_id;
    }

    public function setDomainrelationsId(?int $domainrelations_id): self
    {
        $this->domainrelations_id = $domainrelations_id;

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
