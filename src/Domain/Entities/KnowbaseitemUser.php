<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_knowbaseitems_users')]
#[ORM\Index(name: "knowbaseitems_id", columns: ['knowbaseitems_id'])]
#[ORM\Index(name: "users_id", columns: ['users_id'])]
class KnowbaseitemUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitem::class, inversedBy: 'knowbaseitemUsers')]
    #[ORM\JoinColumn(name: 'knowbaseitems_id', referencedColumnName: 'id', nullable: true)]
    private ?Knowbaseitem $knowbaseitem;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'knowbaseitemUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of knowbaseitem
     */ 
    public function getKnowbaseitem()
    {
        return $this->knowbaseitem;
    }

    /**
     * Set the value of knowbaseitem
     *
     * @return  self
     */ 
    public function setKnowbaseitem($knowbaseitem)
    {
        $this->knowbaseitem = $knowbaseitem;

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
