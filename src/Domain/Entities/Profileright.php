<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_profilerights')]
#[ORM\UniqueConstraint(name: "unicity", columns: ["profiles_id", "name"])]
class Profileright
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $profiles_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $rights;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfilesId(): ?int
    {
        return $this->profiles_id;
    }


    public function setProfilesId(?int $profiles_id): self
    {
        $this->profiles_id = $profiles_id;

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

    public function getRights(): ?int
    {
        return $this->rights;
    }


    public function setRights(?int $rights): self
    {
        $this->rights = $rights;

        return $this;
    }

}
