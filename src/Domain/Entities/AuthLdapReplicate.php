<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_authldapreplicates")]
#[ORM\Index(name: "authldaps_id", columns: ["authldaps_id"])]
class AuthLdapReplicate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $authldaps_id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $host;

    #[ORM\Column(type: "integer", options: ["default" => 389])]
    private $port;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthldapsId(): ?int
    {
        return $this->authldaps_id;
    }

    public function setAuthldapsId(?int $authldaps_id): self
    {
        $this->authldaps_id = $authldaps_id;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;

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
}
