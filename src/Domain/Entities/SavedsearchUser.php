<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_savedsearches_users')]
#[ORM\UniqueConstraint(name: "unicity", columns: ["users_id", "itemtype"])]
#[ORM\Index(name: "savedsearches_id", columns: ["savedsearches_id"])]
class SavedsearchUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'savedsearchUsers')]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: Savedsearch::class, inversedBy: 'savedsearchUsers')]
    #[ORM\JoinColumn(name: 'savedsearches_id', referencedColumnName: 'id', nullable: true)]
    private ?Savedsearch $savedsearch;
    

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

    /**
     * Get the value of savedsearch
     */ 
    public function getSavedsearch()
    {
        return $this->savedsearch;
    }

    /**
     * Set the value of savedsearch
     *
     * @return  self
     */ 
    public function setSavedsearch($savedsearch)
    {
        $this->savedsearch = $savedsearch;

        return $this;
    }
}
