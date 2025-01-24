<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_knowbaseitemtranslations')]
#[ORM\Index(name: "item", columns: ['knowbaseitems_id', 'language'])]
#[ORM\Index(name: "users_id", columns: ['users_id'])]
#[ORM\Index(name: "fulltext", columns: ['name', 'answer'], flags: ['FULLTEXT'])]
#[ORM\Index(name: "name", columns: ['name'], flags: ['FULLTEXT'])]
#[ORM\Index(name: "answer", columns: ['answer'], flags: ['FULLTEXT'])]
class KnowbaseitemTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitem::class)]
    #[ORM\JoinColumn(name: 'knowbaseitems_id', referencedColumnName: 'id', nullable: true)]
    private ?Knowbaseitem $knowbaseitem = null;

    #[ORM\Column(name: 'language', type: 'string', length: 10, nullable: true)]
    private $language;

    #[ORM\Column(name: 'name', type: 'text', nullable: true, length: 65535)]
    private $name;

    #[ORM\Column(name: 'answer', type: 'text', nullable: true)]
    private $answer;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->dateMod;
    }

    public function setDateMod(\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

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

    /**
     * Get the value of knowbaseitem
     */
    public function getKnowbaseitem()
    {
        return $this->knowbaseitem;
    }

    /**
     * Set the value of knowbaseitem
     *
     * @return  self
     */
    public function setKnowbaseitem($knowbaseitem)
    {
        $this->knowbaseitem = $knowbaseitem;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
