<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_planningrecalls')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['itemtype', 'items_id', 'users_id'])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "before_time", columns: ["before_time"])]
#[ORM\Index(name: "when", columns: ["when"])]
class Planningrecall
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;


    #[ORM\Column(type: 'integer', options: ['default' => -10])]
    private $before_time;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $when;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(?int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
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

    public function getBeforeTime(): ?int
    {
        return $this->before_time;
    }

    public function setBeforeTime(?int $before_time): self
    {
        $this->before_time = $before_time;

        return $this;
    }

    public function getWhen(): ?\DateTimeInterface
    {
        return $this->when;
    }

    public function setWhen(\DateTimeInterface $when): self
    {
        $this->when = $when;

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
