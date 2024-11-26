<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_displaypreferences")]
#[ORM\UniqueConstraint(columns: ["users_id", "itemtype", "num"])]
#[ORM\Index(name: "rank", columns: ["rank"])]
#[ORM\Index(name: "num", columns: ["num"])]
#[ORM\Index(name: "itemtype", columns: ["itemtype"])]
class DisplayPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $num;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $rank;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $users_id;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function setNum(int $num): self
    {
        $this->num = $num;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
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
}
