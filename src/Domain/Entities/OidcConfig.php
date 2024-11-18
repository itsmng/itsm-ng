<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_oidc_configs')]
class OidcConfig
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['default'=> 0])]   
    private $id;

    #[ORM\Column(name: 'Provider', type: 'string', length: 255, nullable: true)]
    private $provider;
    
    #[ORM\Column(name: 'ClientID', type: 'string', length: 255, nullable: true)]
    private $clientId;
    
    #[ORM\Column(name: 'ClientSecret', type: 'string', length: 255, nullable: true)]
    private $clientSecret;
    
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_activate;
    
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_forced;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $scope;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $cert;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $proxy;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $logout;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getIsActivate(): ?bool
    {
        return $this->is_activate;
    }

    public function setIsActivate(?bool $is_activate): self
    {
        $this->is_activate = $is_activate;

        return $this;
    }

    public function getIsForced(): ?bool
    {
        return $this->is_forced;
    }

    public function setIsForced(?bool $is_forced): self
    {
        $this->is_forced = $is_forced;

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
