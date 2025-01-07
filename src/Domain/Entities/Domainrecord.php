<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Group;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_domainrecords')]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'entities_id', columns: ['entities_id'])]
#[ORM\Index(name: 'domains_id', columns: ['domains_id'])]
#[ORM\Index(name: 'domainrecordtypes_id', columns: ['domainrecordtypes_id'])]
#[ORM\Index(name: 'users_id_tech', columns: ['users_id_tech'])]
#[ORM\Index(name: 'groups_id_tech', columns: ['groups_id_tech'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'is_deleted', columns: ['is_deleted'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Domainrecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $data;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    #[ORM\ManyToOne(targetEntity: Domain::class)]
    #[ORM\JoinColumn(name: 'domains_id', referencedColumnName: 'id', nullable: true)]
    private ?Domain $domain;

    #[ORM\ManyToOne(targetEntity: DomainrecordType::class)]
    #[ORM\JoinColumn(name: 'domainrecordtypes_id', referencedColumnName: 'id', nullable: true)]
    private ?DomainrecordType $domainrecordtype;


    #[ORM\Column(type: 'integer')]
    private $ttl;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup;


    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_deleted;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private $date_creation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTtl(?int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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
     * Get the value of domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set the value of domain
     *
     * @return  self
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get the value of domainrecordtype
     */
    public function getDomainrecordtype()
    {
        return $this->domainrecordtype;
    }

    /**
     * Set the value of domainrecordtype
     *
     * @return  self
     */
    public function setDomainrecordtype($domainrecordtype)
    {
        $this->domainrecordtype = $domainrecordtype;

        return $this;
    }

    

    /**
     * Get the value of techGroup
     */
    public function getTechGroup()
    {
        return $this->techGroup;
    }

    /**
     * Set the value of techGroup
     *
     * @return  self
     */
    public function setTechGroup($techGroup)
    {
        $this->techGroup = $techGroup;

        return $this;
    }

    /**
     * Get the value of techUser
     */ 
    public function getTechUser()
    {
        return $this->techUser;
    }

    /**
     * Set the value of techUser
     *
     * @return  self
     */ 
    public function setTechUser($techUser)
    {
        $this->techUser = $techUser;

        return $this;
    }
}
