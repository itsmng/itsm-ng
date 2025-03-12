<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_softwarecategories")]
#[ORM\Index(name: "softwarecategories_id", columns: ["softwarecategories_id"])]
class SoftwareCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: SoftwareCategory::class)]
    #[ORM\JoinColumn(name: 'softwarecategories_id', referencedColumnName: 'id', nullable: true)]
    private ?SoftwareCategory $softwarecategory = null;

    #[ORM\Column(name: 'completename', type: 'text', length: 65535, nullable: true)]
    private $completename;

    #[ORM\Column(name: 'level', type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(name: 'ancestors_cache', type: 'text', nullable: true)]
    private $ancestorsCache;

    #[ORM\Column(name: 'sons_cache', type: 'text', nullable: true)]
    private $sonsCache;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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
        return $this->ancestorsCache;
    }

    public function setAncestorsCache(?string $ancestorsCache): self
    {
        $this->ancestorsCache = $ancestorsCache;

        return $this;
    }

    public function getSonsCache(): ?string
    {
        return $this->sonsCache;
    }

    public function setSonsCache(?string $sonsCache): self
    {
        $this->sonsCache = $sonsCache;

        return $this;
    }

    /**
     * Get the value of softwarecategory
     */
    public function getSoftwareCategory()
    {
        return $this->softwarecategory;
    }

    /**
     * Set the value of softwarecategory
     *
     * @return  self
     */
    public function setSoftwareCategory($softwarecategory)
    {
        $this->softwarecategory = $softwarecategory;

        return $this;
    }
}
