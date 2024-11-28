<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_knowbaseitems_profiles')]
#[ORM\Index(name: "knowbaseitems_id", columns: ['knowbaseitems_id'])]
#[ORM\Index(name: "profiles_id", columns: ['profiles_id'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "is_recursive", columns: ['is_recursive'])]
class KnowbaseitemProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $knowbaseitems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $profiles_id;

    #[ORM\Column(type: 'integer', options: ['default' => -1])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    public function getId(): int
    {
        return $this->id;
    }

    public function getKnowbaseitemsId(): int
    {
        return $this->knowbaseitems_id;
    }

    public function setKnowbaseitemsId(int $knowbaseitems_id): self
    {
        $this->knowbaseitems_id = $knowbaseitems_id;

        return $this;
    }

    public function getProfilesId(): int
    {
        return $this->profiles_id;
    }

    public function setProfilesId(int $profiles_id): self
    {
        $this->profiles_id = $profiles_id;

        return $this;
    }

    public function getEntitiesId(): int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }
}
