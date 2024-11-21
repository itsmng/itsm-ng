<?php

namespace Itsm\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name:"glpi_appliancetypes")]
#[ORM\UniqueConstraint(name:"appliancetype_externalidentifier", columns:["externalidentifier"])]
#[ORM\Index(name:"appliancetype_name", columns:["name"])]
#[ORM\Index(name:"appliancetype_entities_id", columns:["entities_id"])]
class ApplianceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private $id;

    #[ORM\Column(type:"integer", options:['default' => 0])]
    private $entities_id;

    #[ORM\Column(type:"boolean", options:['default' => 0])]
    private $is_recursive;

    #[ORM\Column(type:"string", length:255, options:['default' => ""])]
    private $name;

    #[ORM\Column(type:"text", nullable:true, length:65535)]
    private $comment;

    #[ORM\Column(type:"string", length:255, nullable:true)]
    private $externalidentifier;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getExternalIdentifier(): ?string
    {
        return $this->externalidentifier;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setEntitiesId(?int $entitiesId): self
    {
        $this->entities_id = $entitiesId;

        return $this;
    }

    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->is_recursive = $isRecursive;

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setExternalIdentifier(?string $externalIdentifier): self
    {
        $this->externalidentifier = $externalIdentifier;

        return $this;
    }
}
