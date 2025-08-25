<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_oidc_users')]
class OidcUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'user_id', type: 'integer', options: ['default' => 0])]
    private $userId;

    #[ORM\Column(name: 'update', type: 'boolean', options: ['default' => 0])]
    private $update;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUpdate(): ?bool
    {
        return $this->update;
    }

    public function setUpdate(?bool $update): self
    {
        $this->update = $update;

        return $this;
    }

}
