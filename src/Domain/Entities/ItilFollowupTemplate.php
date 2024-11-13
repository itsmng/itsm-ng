<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_itilfollowuptemplates")]
#[ORM\Index(columns: ["name"])]
#[ORM\Index(columns: ["is_recursive"])]
#[ORM\Index(columns: ["requesttypes_id"])]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["date_mod"])]
#[ORM\Index(columns: ["date_creation"])]
#[ORM\Index(columns: ["is_private"])]
class ItilFollowupTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_recursive;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $content;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $requesttypes_id;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_private;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getEntitiesId(): int
    {
        return $this->entities_id;
    }

    public function setIsRecursive(bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }

    public function getIsRecursive(): bool
    {
        return $this->is_recursive;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setRequesttypesId(int $requesttypes_id): self
    {
        $this->requesttypes_id = $requesttypes_id;

        return $this;
    }

    public function getRequesttypesId(): int
    {
        return $this->requesttypes_id;
    }

    public function setIsPrivate(bool $is_private): self
    {
        $this->is_private = $is_private;

        return $this;
    }

    public function getIsPrivate(): bool
    {
        return $this->is_private;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}

