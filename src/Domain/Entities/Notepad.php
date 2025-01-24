<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_notepads')]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date', columns: ['date'])]
#[ORM\Index(name: 'users_id_lastupdater', columns: ['users_id_lastupdater'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
class Notepad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100, nullable: true)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private $date;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'users_id_lastupdater', type: 'integer', options: ['default' => 0])]
    private $usersIdLastupdater;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_lastupdater', referencedColumnName: 'id', nullable: true)]
    private ?User $userLastupdater = null;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(?int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getUsersIdLastupdater(): ?int
    {
        return $this->usersIdLastupdater;
    }

    public function setUsersIdLastupdater(?int $usersIdLastupdater): self
    {
        $this->usersIdLastupdater = $usersIdLastupdater;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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
     * Get the value of userLastupdater
     */
    public function getUserLastupdater()
    {
        return $this->userLastupdater;
    }

    /**
     * Set the value of userLastupdater
     *
     * @return  self
     */
    public function setUserLastupdater($userLastupdater)
    {
        $this->userLastupdater = $userLastupdater;

        return $this;
    }
}
