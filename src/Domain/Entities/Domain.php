<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "glpi_domains")]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "domaintypes_id", columns: ["domaintypes_id"])]
#[ORM\Index(name: "tech_users_id", columns: ["tech_users_id"])]
#[ORM\Index(name: "tech_groups_id", columns: ["tech_groups_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "is_deleted", columns: ["is_deleted"])]
#[ORM\Index(name: "date_expiration", columns: ["date_expiration"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Domain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => false])]
    private $isRecursive;

    #[ORM\ManyToOne(targetEntity: Domaintype::class)]
    #[ORM\JoinColumn(name: 'domaintypes_id', referencedColumnName: 'id', nullable: true)]
    private ?Domaintype $domaintype = null;

    #[ORM\Column(name: 'date_expiration', type: "datetime", nullable: true)]
    private $dateExpiration;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'tech_users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $techUser = null;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'tech_groups_id', referencedColumnName: 'id', nullable: true)]
    private ?Group $techGroup = null;

    #[ORM\Column(name: 'others', type: "string", length: 255, nullable: true)]
    private $others;

    #[ORM\Column(name: 'is_deleted', type: "boolean", options: ["default" => false])]
    private $isDeleted;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'domain', targetEntity: DomainItem::class)]
    private Collection $domainItems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): self
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function getOthers(): ?string
    {
        return $this->others;
    }

    public function setOthers(string $others): self
    {
        $this->others = $others;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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
     * Get the value of domaintype
     */
    public function getDomaintype()
    {
        return $this->domaintype;
    }

    /**
     * Set the value of domaintype
     *
     * @return  self
     */
    public function setDomaintype($domaintype)
    {
        $this->domaintype = $domaintype;

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
     * Get the value of domainItems
     */
    public function getDomainItems()
    {
        return $this->domainItems;
    }

    /**
     * Set the value of domainItems
     *
     * @return  self
     */
    public function setDomainItems($domainItems)
    {
        $this->domainItems = $domainItems;

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
