<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_groups_rssfeeds")]
#[ORM\Index(columns: ["rssfeeds_id"])]
#[ORM\Index(columns: ["groups_id"])]
#[ORM\Index(columns: ["entities_id"])]
#[ORM\Index(columns: ["is_recursive"])]
class GroupRssFeed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $rssfeeds_id;

    #[ORM\Column(type: "integer", options: ['default' => 0])]
    private $groups_id;

    #[ORM\Column(type: "integer", options: ['default' => -1])]
    private $entities_id;

    #[ORM\Column(type: "boolean", options: ['default' => false])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGroupsId(): ?int
    {
        return $this->groups_id;
    }

    public function setGroupsId(int $groups_id): self
    {
        $this->groups_id = $groups_id;

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
}