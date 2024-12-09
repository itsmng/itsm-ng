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


    #[ORM\ManyToOne(targetEntity: AuthLdap::class)]
    #[ORM\JoinColumn(name: 'authldaps_id', referencedColumnName: 'id', nullable: true)]
    private ?AuthLdap $authldap;

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

    /**
     * Get the value of authldap
     */
    public function getAuthldap()
    {
        return $this->authldap;
    }

    /**
     * Set the value of authldap
     *
     * @return  self
     */
    public function setAuthldap($authldap)
    {
        $this->authldap = $authldap;

        return $this;
    }
}
