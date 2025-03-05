<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_documents_items')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['documents_id', 'itemtype', 'items_id', 'timeline_position'])]
#[ORM\Index(name: 'item', columns: ['itemtype', 'items_id', 'entities_id', 'is_recursive'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date', columns: ['date'])]
class DocumentItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Document::class, inversedBy: 'documentItems')]
    #[ORM\JoinColumn(name: 'documents_id', referencedColumnName: 'id', nullable: true)]
    private ?Document $document = null;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: 'boolean', options: ['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: false)]
    private $dateMod;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'timeline_position', type: 'boolean', options: ['default' => 0])]
    private $timelinePosition;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    private $dateCreation;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: false)]
    private $date;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(?string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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

    public function getTimelinePosition(): ?bool
    {
        return $this->timelinePosition;
    }

    public function setTimelinePosition(?bool $timelinePosition): self
    {
        $this->timelinePosition = $timelinePosition;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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


    /**
     * Get the value of document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the value of document
     *
     * @return  self
     */
    public function setDocument($document)
    {
        $this->document = $document;

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
