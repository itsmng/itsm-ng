<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_knowbaseitems_comments")]
class KnowbaseitemComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer")]
    private $knowbaseitems_id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitem::class)]
    #[ORM\JoinColumn(name: 'knowbaseitems_id', referencedColumnName: 'id', nullable: true)]
    private ?Knowbaseitem $knowbaseitem;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private $language;

    #[ORM\Column(type: "text", length: 65535)]
    private $comment;

    #[ORM\Column(type: "integer", nullable: true)]
    private $parent_comment_id;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_creation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKnowbaseitemsId(): ?int
    {
        return $this->knowbaseitems_id;
    }

    public function setKnowbaseitemsId(int $knowbaseitems_id): self
    {
        $this->knowbaseitems_id = $knowbaseitems_id;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getParentCommentId(): ?int
    {
        return $this->parent_comment_id;
    }

    public function setParentCommentId(?int $parent_comment_id): self
    {
        $this->parent_comment_id = $parent_comment_id;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

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
}
