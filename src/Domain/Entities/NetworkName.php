<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "glpi_networknames")]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "fqdn", columns: ["name", "fqdns_id"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "fqdns_id", columns: ["fqdns_id"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "is_dynamic", columns: ["is_dynamic"])]
#[ORM\Index(name: "item", columns: ["itemtype", "items_id", "is_deleted"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class NetworkName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'items_id', type: "integer", options: ["default" => 0])]
    private $itemsId = 0;

    #[ORM\Column(name: 'itemtype', type: "string", length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: FQDN::class)]
    #[ORM\JoinColumn(name: 'fqdns_id', referencedColumnName: 'id', nullable: true)]
    private ?FQDN $fqdn = null;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => 0])]
    private $isDeleted = 0;

    #[ORM\Column(name: 'is_dynamic', type: "boolean", options: ["default" => 0])]
    private $isDynamic = 0;

    #[ORM\Column(name: 'date_mod', type: "datetime")]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime")]
    private $dateCreation;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }


    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

        return $this;
    }

    /**
     * Get the value of entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of fqdn
     */
    public function getFQDN()
    {
        return $this->fqdn;
    }

    /**
     * Set the value of fqdn
     *
     * @return  self
     */
    public function setFQDN($fqdn)
    {
        $this->fqdn = $fqdn;

        return $this;
    }
}
