<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_apiclients')]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'is_active', columns: ['is_active'])]
class ApiClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_active;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private $ipv4_range_start;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private $ipv4_range_end;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $ipv6;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $app_token;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $app_token_date;

    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    private $dolog_method;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getIpv4RangeStart(): ?int
    {
        return $this->ipv4_range_start;
    }

    public function setIpv4RangeStart(int $ipv4_range_start): self
    {
        $this->ipv4_range_start = $ipv4_range_start;

        return $this;
    }

    public function getIpv4RangeEnd(): ?int
    {
        return $this->ipv4_range_end;
    }

    public function setIpv4RangeEnd(int $ipv4_range_end): self
    {
        $this->ipv4_range_end = $ipv4_range_end;

        return $this;
    }

    public function getIpv6(): ?string
    {
        return $this->ipv6;
    }

    public function setIpv6(?string $ipv6): self
    {
        $this->ipv6 = $ipv6;

        return $this;
    }

    public function getAppToken(): ?string
    {
        return $this->app_token;
    }

    public function setAppToken(?string $app_token): self
    {
        $this->app_token = $app_token;

        return $this;
    }

    public function getAppTokenDate(): ?\DateTimeInterface
    {
        return $this->app_token_date;
    }

    public function setAppTokenDate(\DateTimeInterface $app_token_date): self
    {
        $this->app_token_date = $app_token_date;

        return $this;
    }

    public function getDologMethod(): ?int
    {
        return $this->dolog_method;
    }

    public function setDologMethod(int $dolog_method): self
    {
        $this->dolog_method = $dolog_method;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
