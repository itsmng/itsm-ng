<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_devicesimcards')]
#[ORM\Index(columns: ['itemtype', 'items_id'])]
#[ORM\Index(columns: ['devicesimcards_id'])]
#[ORM\Index(columns: ['is_deleted'])]
#[ORM\Index(columns: ['is_dynamic'])]
#[ORM\Index(columns: ['entities_id'])]
#[ORM\Index(columns: ['is_recursive'])]
#[ORM\Index(columns: ['serial'])]
#[ORM\Index(columns: ['otherserial'])]
#[ORM\Index(columns: ['states_id'])]
#[ORM\Index(columns: ['locations_id'])]
#[ORM\Index(columns: ['lines_id'])]
#[ORM\Index(columns: ['users_id'])]
#[ORM\Index(columns: ['groups_id'])]
class ItemDeviceSimCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0, 'comment' => 'RELATION to various table, according to itemtype (id)'])]
    private $items_id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $devicesimcards_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_deleted;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_dynamic;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $states_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $locations_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $lines_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $pin;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $pin2;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $puk;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $puk2;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => ''])]
    private $msin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

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

    public function getDevicesimcardsId(): ?int
    {
        return $this->devicesimcards_id;
    }

    public function setDevicesimcardsId(int $devicesimcards_id): self
    {
        $this->devicesimcards_id = $devicesimcards_id;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->is_dynamic;
    }

    public function setIsDynamic(bool $is_dynamic): self
    {
        $this->is_dynamic = $is_dynamic;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getOtherSerial(): ?string
    {
        return $this->otherserial;
    }

    public function setOtherSerial(string $other_serial): self
    {
        $this->otherserial = $other_serial;

        return $this;
    }

    public function getStatesId(): ?int
    {
        return $this->states_id;
    }

    public function setStatesId(int $states_id): self
    {
        $this->states_id = $states_id;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locations_id;
    }

    public function setLocationsId(int $locations_id): self
    {
        $this->locations_id = $locations_id;

        return $this;
    }

    public function getLinesId(): ?int
    {
        return $this->lines_id;
    }

    public function setLinesId(int $lines_id): self
    {
        $this->lines_id = $lines_id;

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

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(int $groups_id): self
    {
        $this->groups_id = $groups_id;

        return $this;
    }

    public function getPin(): ?string
    {
        return $this->pin;
    }

    public function setPin(string $pin): self
    {
        $this->pin = $pin;

        return $this;
    }

    public function getPin2(): ?string
    {
        return $this->pin2;
    }

    public function setPin2(string $pin2): self
    {
        $this->pin2 = $pin2;

        return $this;
    }

    public function getPuk(): ?string
    {
        return $this->puk;
    }

    public function setPuk(string $puk): self
    {
        $this->puk = $puk;

        return $this;
    }

    public function getPuk2(): ?string
    {
        return $this->puk2;
    }

    public function setPuk2(string $puk2): self
    {
        $this->puk2 = $puk2;

        return $this;
    }

    public function getMsin(): ?string
    {
        return $this->msin;
    }

    public function setMsin(string $msin): self
    {
        $this->msin = $msin;

        return $this;
    }
}
