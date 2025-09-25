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
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => false])]
    private $isRecursive = false;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => false])]
    private $isActive = false;

    #[ORM\Column(name: 'ipv4_range_start', type: 'bigint', nullable: true)]
    private $ipv4_range_start;

    #[ORM\Column(name: 'ipv4_range_end', type: 'bigint', nullable: true)]
    private $ipv4_range_end;

    #[ORM\Column(name: 'ipv6', type: 'string', length: 255, nullable: true)]
    private $ipv6;

    #[ORM\Column(name: 'app_token', type: 'string', length: 255, nullable: true)]
    private $appToken;

    #[ORM\Column(name: 'app_token_date', type: 'datetime', nullable: true)]
    private $appTokenDate;

    #[ORM\Column(name: 'dolog_method', type: 'smallint', options: ['default' => 0])]
    private $dologMethod = 0;

    #[ORM\Column(name: 'comment', type: 'text', nullable: true, length: 65535)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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
        return $this->appToken;
    }

    public function setAppToken(?string $appToken): self
    {
        $this->appToken = $appToken;

        return $this;
    }

    public function getAppTokenDate(): ?\DateTimeInterface
    {
        return $this->appTokenDate;
    }

    public function setAppTokenDate(\DateTimeInterface $appTokenDate): self
    {
        $this->appTokenDate = $appTokenDate;

        return $this;
    }

    public function getDologMethod(): ?int
    {
        return $this->dologMethod;
    }

    public function setDologMethod(int $dologMethod): self
    {
        $this->dologMethod = $dologMethod;

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

    public function getEntity()
    {
        return $this->entity;
    }


    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }
}
