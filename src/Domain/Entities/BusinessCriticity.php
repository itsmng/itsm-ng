<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_businesscriticities")]
#[ORM\UniqueConstraint(name: "businesscriticities_name_unique", columns: ["businesscriticities_id", "name"])]
#[ORM\Index(name: "businesscriticities_name_index", columns: ["name"])]
#[ORM\Index(name: "businesscriticities_date_mod_index", columns: ["date_mod"])]
#[ORM\Index(name: "businesscriticities_date_creation_index", columns: ["date_creation"])]
class BusinessCriticity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "integer", name: 'entities_id', options: ["default" => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'businesscriticities')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\Column(type: "integer", name: 'businesscriticities_id', options: ["default" => 0])]
    private $businesscriticities_id;

    #[ORM\ManyToOne(targetEntity: BusinessCriticity::class, inversedBy: 'businesscriticities')]
    #[ORM\JoinColumn(name: 'businesscriticities_id', referencedColumnName: 'id', nullable: false)]
    private ?BusinessCriticity $businessCriticity;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $completename;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $level;

    #[ORM\Column(type: "text", nullable: true)]
    private $ancestors_cache;

    #[ORM\Column(type: "text", nullable: true)]
    private $sons_cache;

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

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;
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

    public function getBusinesscriticitiesId(): ?int
    {
        return $this->businesscriticities_id;
    }

    public function setBusinesscriticitiesId(?int $businesscriticities_id): self
    {
        $this->businesscriticities_id = $businesscriticities_id;
        return $this;
    }

    public function getCompletename(): ?string
    {
        return $this->completename;
    }

    public function setCompletename(?string $completename): self
    {
        $this->completename = $completename;
        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;
        return $this;
    }

    public function getAncestorsCache(): ?string
    {
        return $this->ancestors_cache;
    }

    public function setAncestorsCache(?string $ancestors_cache): self
    {
        $this->ancestors_cache = $ancestors_cache;
        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sons_cache;
    }

    public function setSonsCache(?string $sons_cache): self
    {
        $this->sons_cache = $sons_cache;
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
