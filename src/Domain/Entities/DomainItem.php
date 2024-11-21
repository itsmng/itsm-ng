<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_domains_items')]
#[ORM\UniqueConstraint(name: 'domains_id_itemtype_items_id', columns: ['domains_id', 'itemtype', 'items_id'])]
#[ORM\Index(name: 'domains_id', columns: ['domains_id'])]
#[ORM\Index(name: 'domainrelations_id', columns: ['domainrelations_id'])]
#[ORM\Index(name: 'items_id_itemtype', columns: ['items_id', 'itemtype'])]
#[ORM\Index(name: 'itemtype_items_id', columns: ['itemtype', 'items_id'])]
class DomainItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $domains_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $domainrelations_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomainsId(): ?int
    {
        return $this->domains_id;
    }

    public function setDomainsId(?int $domains_id): self
    {
        $this->domains_id = $domains_id;

        return $this;
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

}