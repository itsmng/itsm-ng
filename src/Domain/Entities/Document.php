<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_documents')]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'tickets_id', columns: ['tickets_id'])]
#[ORM\Index(name: 'users_id', columns: ['users_id'])]
#[ORM\Index(name: 'documentcategories_id', columns: ['documentcategories_id'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'sha1sum', columns: ['sha1sum'])]
#[ORM\Index(name: 'tag', columns: ['tag'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_recursive;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['comment' => 'for display and transfert'])]
    private $filename;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['comment' => 'file storage path'])]
    private $filepath;

    #[ORM\ManyToOne(targetEntity: Documentcategory::class)]
    #[ORM\JoinColumn(name: 'documentcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Documentcategory $documentcategory;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $mime;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'text', nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_deleted;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $link;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(name: 'tickets_id', referencedColumnName: 'id', nullable: true)]
    private ?Ticket $ticket;

    #[ORM\Column(type: 'string', length: 40, nullable: true, options: ['fixed' => true])]
    private $sha1sum;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $is_blacklisted;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $tag;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'document', targetEntity: DocumentItem::class)]
    private Collection $documentItems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath): self
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function setMime(string $mime): self
    {
        $this->mime = $mime;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getSha1sum(): ?string
    {
        return $this->sha1sum;
    }

    public function setSha1sum(string $sha1sum): self
    {
        $this->sha1sum = $sha1sum;

        return $this;
    }

    public function getIsBlacklisted(): ?bool
    {
        return $this->is_blacklisted;
    }

    public function setIsBlacklisted(bool $is_blacklisted): self
    {
        $this->is_blacklisted = $is_blacklisted;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    /**
     * Get the value of documentcategory
     */
    public function getDocumentcategory()
    {
        return $this->documentcategory;
    }

    /**
     * Set the value of documentcategory
     *
     * @return  self
     */
    public function setDocumentcategory($documentcategory)
    {
        $this->documentcategory = $documentcategory;

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

    /**
     * Get the value of ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * Set the value of ticket
     *
     * @return  self
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * Get the value of documentItems
     */
    public function getDocumentItems()
    {
        return $this->documentItems;
    }

    /**
     * Set the value of documentItems
     *
     * @return  self
     */
    public function setDocumentItems($documentItems)
    {
        $this->documentItems = $documentItems;

        return $this;
    }
}
