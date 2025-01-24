<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_items_devicesimcards')]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "devicesimcards_id", columns: ['devicesimcards_id'])]
#[ORM\Index(name: "is_deleted", columns: ['is_deleted'])]
#[ORM\Index(name: "is_dynamic", columns: ['is_dynamic'])]
#[ORM\Index(name: "entities_id", columns: ['entities_id'])]
#[ORM\Index(name: "is_recursive", columns: ['is_recursive'])]
#[ORM\Index(name: "serial", columns: ['serial'])]
#[ORM\Index(name: "otherserial", columns: ['otherserial'])]
#[ORM\Index(name: "states_id", columns: ['states_id'])]
#[ORM\Index(name: "locations_id", columns: ['locations_id'])]
#[ORM\Index(name: "lines_id", columns: ['lines_id'])]
#[ORM\Index(name: "users_id", columns: ['users_id'])]
#[ORM\Index(name: "groups_id", columns: ['groups_id'])]
class ItemDeviceSimCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0, 'comment' => 'RELATION to various table, according to itemtype (id)'])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'devicesimcards_id', type: 'integer', options: ['default' => 0])]
    private $devicesimcardsId;

    #[ORM\Column(name: 'is_deleted', type: 'boolean', options: ['default' => false])]
    private $isDeleted;

    #[ORM\Column(name: 'is_dynamic', type: 'boolean', options: ['default' => false])]
    private $isDynamic;

    #[ORM\Column(name: 'entities_id', type: 'integer', options: ['default' => 0])]
    private $entitiesId;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => false])]
    private $isRecursive;

    #[ORM\Column(name: 'serial', type: 'string', length: 255, nullable: true)]
    private $serial;

    #[ORM\Column(name: 'otherserial', type: 'string', length: 255, nullable: true)]
    private $otherserial;

    #[ORM\Column(name: 'states_id', type: 'integer', options: ['default' => 0])]
    private $statesId;

    #[ORM\Column(name: 'locations_id', type: 'integer', options: ['default' => 0])]
    private $locationsId;

    #[ORM\Column(name: 'lines_id', type: 'integer', options: ['default' => 0])]
    private $linesId;

    #[ORM\Column(name: 'users_id', type: 'integer', options: ['default' => 0])]
    private $usersId;

    #[ORM\Column(name: 'groups_id', type: 'integer', options: ['default' => 0])]
    private $groupsId;

    #[ORM\Column(name: 'pin', type: 'string', length: 255, options: ['default' => ''])]
    private $pin;

    #[ORM\Column(name: 'pin2', type: 'string', length: 255, options: ['default' => ''])]
    private $pin2;

    #[ORM\Column(name: 'puk', type: 'string', length: 255, options: ['default' => ''])]
    private $puk;

    #[ORM\Column(name: 'puk2', type: 'string', length: 255, options: ['default' => ''])]
    private $puk2;

    #[ORM\Column(name: 'msin', type: 'string', length: 255, options: ['default' => ''])]
    private $msin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemsId(): ?int
    {
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

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
        return $this->devicesimcardsId;
    }

    public function setDevicesimcardsId(int $devicesimcardsId): self
    {
        $this->devicesimcardsId = $devicesimcardsId;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDynamic(): ?bool
    {
        return $this->isDynamic;
    }

    public function setIsDynamic(bool $isDynamic): self
    {
        $this->isDynamic = $isDynamic;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entitiesId;
    }

    public function setEntitiesId(int $entitiesId): self
    {
        $this->entitiesId = $entitiesId;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function setOtherSerial(string $otherSerial): self
    {
        $this->otherserial = $otherSerial;

        return $this;
    }

    public function getStatesId(): ?int
    {
        return $this->statesId;
    }

    public function setStatesId(int $statesId): self
    {
        $this->statesId = $statesId;

        return $this;
    }

    public function getLocationsId(): ?int
    {
        return $this->locationsId;
    }

    public function setLocationsId(int $locationsId): self
    {
        $this->locationsId = $locationsId;

        return $this;
    }

    public function getLinesId(): ?int
    {
        return $this->linesId;
    }

    public function setLinesId(int $linesId): self
    {
        $this->linesId = $linesId;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->usersId;
    }

    public function setUsersId(int $usersId): self
    {
        $this->usersId = $usersId;

        return $this;
    }

    public function getGroupsId(): ?int
    {
        return $this->groupsId;
    }

    public function setGroupsId(int $groupsId): self
    {
        $this->groupsId = $groupsId;

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
