<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_documentcategories')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['documentcategories_id', 'name'])]
#[ORM\Index(name: 'name', columns: ['name'])]
#[ORM\Index(name: 'date_mod', columns: ['date_mod'])]
#[ORM\Index(name: 'date_creation', columns: ['date_creation'])]
class Documentcategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'integer', name: 'documentcategories_id', options: ['default' => 0])]
    private $documentcategories_id;

    #[ORM\ManyToOne(targetEntity: Documentcategory::class)]
    #[ORM\JoinColumn(name: 'documentcategories_id', referencedColumnName: 'id', nullable: false)]
    private ?Documentcategory $documentcategory;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $completename;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $level;

    #[ORM\Column(type: 'text', nullable: true)]
    private $ancestors_cache;

    #[ORM\Column(type: 'text', nullable: true)]
    private $sons_cache;

    #[ORM\Column(type: 'datetime', nullable: 'false')]
    #[ORM\Version]
    private $date_mod;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'], nullable: true)]
    private $date_creation;

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

    public function getDocumentcategoriesId(): ?int
    {
        return $this->documentcategories_id;
    }

    public function setDocumentcategoriesId(?int $documentcategories_id): self
    {
        $this->documentcategories_id = $documentcategories_id;

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


    /**
     * Get the value of documentcategory
     */
    public function getDocumentcategory()
    {
        return $this->documentcategory;
    }

    /**
     * Set the value of documentcategory
     *
     * @return  self
     */
    public function setDocumentcategory($documentcategory)
    {
        $this->documentcategory = $documentcategory;

        return $this;
    }
}
