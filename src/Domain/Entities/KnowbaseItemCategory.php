<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_knowbaseitemcategories")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["entities_id", "knowbaseitemcategories_id", "name"])]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class KnowbaseItemCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\Column(name: 'entities_id', type: "integer", options: ["default" => 0])]
    private $entitiesId;

    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'knowbaseitemcategories_id', type: "integer", options: ["default" => 0])]
    private $knowbaseitemcategoriesId;

    #[ORM\Column(name: 'name', type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'completename', type: "text", nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(name: 'comment', type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(name: 'level', type: "integer", options: ["default" => 0])]
    private $level;

    #[ORM\Column(name: 'sons_cache', type: "text", nullable: true)]
    private $sonsCache;

    #[ORM\Column(name: 'ancestors_cache', type: "text", nullable: true)]
    private $ancestorsCache;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: "datetime", nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getKnowbaseItemcategoriesId(): ?int
    {
        return $this->knowbaseitemcategoriesId;
    }

    public function setKnowbaseItemcategoriesId(int $knowbaseitemcategoriesId): self
    {
        $this->knowbaseitemcategoriesId = $knowbaseitemcategoriesId;

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

    public function getCompletename(): ?string
    {
        return $this->completename;
    }

    public function setCompletename(string $completename): self
    {
        $this->completename = $completename;

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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sonsCache;
    }

    public function setSonsCache(string $sonsCache): self
    {
        $this->sonsCache = $sonsCache;

        return $this;
    }

    public function getAncestorsCache(): ?string
    {
        return $this->ancestorsCache;
    }

    public function setAncestorsCache(string $ancestorsCache): self
    {
        $this->ancestorsCache = $ancestorsCache;

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
}
