<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_savedsearches_users')]
#[ORM\UniqueConstraint(name: "users_id_itemtype", columns: ["users_id", "itemtype"])]
#[ORM\Index(name: "savedsearches_id", columns: ["savedsearches_id"])]
class SavedsearchUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $savedsearches_id;  

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getSavedsearchesId(): ?int
    {
        return $this->savedsearches_id;
    }

    public function setSavedsearchesId(int $savedsearches_id): self
    {
        $this->savedsearches_id = $savedsearches_id;

        return $this;
    }       

}