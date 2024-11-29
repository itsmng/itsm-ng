<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_entities_rssfeeds")]
#[ORM\Index(name: "rssfeeds_id", columns: ["rssfeeds_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class EntityRssFeed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", name: "rssfeeds_id", options: ["default" => 0])]
    private $rssfeeds_id;

    #[ORM\ManyToOne(targetEntity: Rssfeed::class, inversedBy: 'entityRssfeeds')]
    #[ORM\JoinColumn(name: 'rssfeeds_id', referencedColumnName: 'id', nullable: false)]
    private ?Rssfeed $rssfeed;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $entities_id;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'entityRssfeeds')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: false)]
    private ?Entity $entity;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRssfeedsId(): ?int
    {
        return $this->rssfeeds_id;
    }

    public function setRssfeedsId(int $rssfeeds_id): self
    {
        $this->rssfeeds_id = $rssfeeds_id;

        return $this;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }

    public function setEntitiesId(int $entities_id): self
    {
        $this->entities_id = $entities_id;

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

    /**
     * Get the value of rssfeed
     */ 
    public function getRssfeed()
    {
        return $this->rssfeed;
    }

    /**
     * Set the value of rssfeed
     *
     * @return  self
     */ 
    public function setRssfeed($rssfeed)
    {
        $this->rssfeed = $rssfeed;

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
