<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_reservations')]
#[ORM\Index(name: "begin", columns: ["begin"])]
#[ORM\Index(name: "end", columns: ["end"])]
#[ORM\Index(name: "reservationitems_id", columns: ["reservationitems_id"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "resagroup", columns: ["reservationitems_id", "group"])]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: ReservationItem::class)]
    #[ORM\JoinColumn(name: 'reservationitems_id', referencedColumnName: 'id', nullable: true)]
    private ?ReservationItem $reservationitem = null;

    #[ORM\Column(name: 'begin', type: 'datetime', nullable: true)]
    private $begin;

    #[ORM\Column(name: 'end', type: 'datetime', nullable: true)]
    private $end;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'group', type: 'integer', options: ['default' => 0])]
    private $group;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBegin(): ?string
    {
        return $this->begin;
    }

    public function setBegin(?string $begin): self
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(?string $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): self
    {
        $this->group = $group;

        return $this;
    }


    /**
     * Get the value of reservationitem
     */
    public function getReservationItem()
    {
        return $this->reservationitem;
    }

    /**
     * Set the value of reservationitem
     *
     * @return  self
     */
    public function setReservationItem($reservationitem)
    {
        $this->reservationitem = $reservationitem;

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
