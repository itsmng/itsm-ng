<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_rssfeeds_users')]
#[ORM\Index(name: "rssfeeds_id", columns: ["rssfeeds_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
class RssfeedUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $rssfeeds_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRssfeedsId(): ?string
    {
        return $this->rssfeeds_id;
    }

    public function setRssfeedsId(?string $rssfeeds_id): self
    {
        $this->rssfeeds_id = $rssfeeds_id;

        return $this;
    }

    public function getUsersId(): ?string
    {
        return $this->users_id;
    }

    public function setUsersId(?string $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

}   
