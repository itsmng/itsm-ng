<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_oidc_config')]
class OidcConfig
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', options: ['default' => 0])]
    private $id = 0;

    #[ORM\Column(name: 'Provider', type: 'string', length: 255, nullable: true)]
    private $Provider = '';

    #[ORM\Column(name: 'clientid', type: 'string', length: 255, nullable: true)]
    private $ClientId = '';

    #[ORM\Column(name: 'clientsecret', type: 'string', length: 255, nullable: true)]
    private $ClientSecret = '';

    #[ORM\Column(name: 'is_activate', type: 'boolean', options: ['default' => 0])]
    private $isActivate = 0;

    #[ORM\Column(name: 'is_forced', type: 'boolean', options: ['default' => 0])]
    private $isForced = 0;

    #[ORM\Column(name: 'scope', type: 'string', length: 255, nullable: true)]
    private $scope = '';

    #[ORM\Column(name: 'cert', type: 'string', length: 255, nullable: true)]
    private $cert = '';

    #[ORM\Column(name: 'proxy', type: 'string', length: 255, nullable: true)]
    private $proxy = '';

    #[ORM\Column(name: 'logout', type: 'string', length: 255, nullable: true)]
    private $logout = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): ?string
    {
        return $this->Provider;
    }

    public function setProvider(?string $Provider): self
    {
        $this->Provider = $Provider;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->ClientId;
    }

    public function setClientId(?string $ClientId): self
    {
        $this->ClientId = $ClientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->ClientSecret;
    }

    public function setClientSecret(?string $ClientSecret): self
    {
        $this->ClientSecret = $ClientSecret;

        return $this;
    }

    public function getIsActivate(): ?bool
    {
        return $this->isActivate;
    }

    public function setIsActivate(?bool $isActivate): self
    {
        $this->isActivate = $isActivate;

        return $this;
    }

    public function getIsForced(): ?bool
    {
        return $this->isForced;
    }

    public function setIsForced(?bool $isForced): self
    {
        $this->isForced = $isForced;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getCert(): ?string
    {
        return $this->cert;
    }

    public function setCert(?string $cert): self
    {
        $this->cert = $cert;

        return $this;
    }

    public function getProxy(): ?string
    {
        return $this->proxy;
    }

    public function setProxy(?string $proxy): self
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function getLogout(): ?string
    {
        return $this->logout;
    }

    public function setLogout(?string $logout): self
    {
        $this->logout = $logout;

        return $this;
    }

}
