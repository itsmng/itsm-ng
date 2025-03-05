<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_knowbaseitems_revisions')]
#[ORM\UniqueConstraint(name: "unicity", columns: ['knowbaseitems_id', 'revision', 'language'])]
#[ORM\Index(name: "revision", columns: ['revision'])]
class KnowbaseitemRevision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitem::class, inversedBy: 'knowbaseitemProfiles')]
    #[ORM\JoinColumn(name: 'knowbaseitems_id', referencedColumnName: 'id', nullable: true)]
    private ?Knowbaseitem $knowbaseitem = null;

    #[ORM\Column(name: 'revision', type: 'integer')]
    private $revision;

    #[ORM\Column(name: 'name', type: 'text', nullable: true, length: 65535)]
    private $name;

    #[ORM\Column(name: 'answer', type: 'text', nullable: true)]
    private $answer;

    #[ORM\Column(name: 'language', type: 'string', length: 10, nullable: true)]
    private $language;

    #[ORM\Column(name: 'users_id', type: 'integer', options: ['default' => 0])]
    private $usersId;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getRevision(): ?int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): self
    {
        $this->revision = $revision;

        return $this;
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

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->usersId;
    }

    public function setUsersId(?int $usersId): self
    {
        $this->usersId = $usersId;

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
}
