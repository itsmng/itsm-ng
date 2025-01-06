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

    #[ORM\ManyToOne(targetEntity: Rssfeed::class, inversedBy: 'rssfeedUsers')]
    #[ORM\JoinColumn(name: 'rssfeeds_id', referencedColumnName: 'id', nullable: true)]
    private ?Rssfeed $rssfeed;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rssfeedUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of rssfeed
     */
    public function getRssfeed()
    {
        return $this->rssfeed;
    }

    /**
     * Set the value of rssfeed
     *
     * @return  self
     */
    public function setRssfeed($rssfeed)
    {
        $this->rssfeed = $rssfeed;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
