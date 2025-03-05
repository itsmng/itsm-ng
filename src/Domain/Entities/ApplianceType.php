<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name:"glpi_appliancetypes")]
#[ORM\UniqueConstraint(name: "externalidentifier", columns:["externalidentifier"])]
#[ORM\Index(name: "name", columns:["name"])]
#[ORM\Index(name: "entities_id", columns:["entities_id"])]
class ApplianceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type:"integer")]
    private $id;


    #[ORM\ManyToOne(targetEntity: Entity::class)]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type:"boolean", options:['default' => 0])]
    private $isRecursive;

    #[ORM\Column(name: 'name', type:"string", length:255, options:['default' => ""])]
    private $name;

    #[ORM\Column(name: 'comment', type:"text", nullable:true, length:65535)]
    private $comment;

    #[ORM\Column(name: 'externalidentifier', type:"string", length:255, nullable:true)]
    private $externalidentifier;

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getIsRecursive(): ?bool
    {
        return $this->isRecursive;
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


    public function setIsRecursive(?bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
