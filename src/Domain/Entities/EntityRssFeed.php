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
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Rssfeed::class, inversedBy: 'entityRssfeeds')]
    #[ORM\JoinColumn(name: 'rssfeeds_id', referencedColumnName: 'id', nullable: true)]
    private ?Rssfeed $rssfeed = null;

    #[ORM\ManyToOne(targetEntity: Entity::class, inversedBy: 'entityRssfeeds')]
    #[ORM\JoinColumn(name: 'entities_id', referencedColumnName: 'id', nullable: true)]
    private ?Entity $entity = null;

    #[ORM\Column(name: 'is_recursive', type: "boolean", options: ["default" => false])]
    private $isRecursive;

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
        return $this->isRecursive;
    }

    public function setIsRecursive(bool $isRecursive): self
    {
        $this->isRecursive = $isRecursive;

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
