<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_knowbaseitems_users')]
#[ORM\Index(columns: ['knowbaseitems_id'])]
#[ORM\Index(columns: ['users_id'])]
class KnowbaseitemUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $knowbaseitems_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

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

    public function getUsersId(): int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }
}
