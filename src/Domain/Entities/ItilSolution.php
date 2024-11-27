<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_itilsolutions')]
#[ORM\Index(name: "itemtype", columns: ['itemtype'])]
#[ORM\Index(name: "items_id", columns: ['items_id'])]
#[ORM\Index(name: "item", columns: ['itemtype', 'items_id'])]
#[ORM\Index(name: "solutiontypes_id", columns: ['solutiontypes_id'])]
#[ORM\Index(name: "users_id", columns: ['users_id'])]
#[ORM\Index(name: "users_id_editor", columns: ['users_id_editor'])]
#[ORM\Index(name: "users_id_approval", columns: ['users_id_approval'])]
#[ORM\Index(name: "status", columns: ['status'])]
#[ORM\Index(name: "itilfollowups_id", columns: ['itilfollowups_id'])]
class ItilSolution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $items_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $solutiontypes_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $solutiontype_name;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_approval;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $user_name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_editor;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id_approval;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $user_name_approval;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private $status;

    #[ORM\Column(type: 'integer', nullable: true, options: ['comment' => 'Followup reference on reject or approve a solution'])]
    private $itilfollowups_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemtype(): ?string
    {
        return $this->itemtype;
    }

    public function setItemtype(string $itemtype): self
    {
        $this->itemtype = $itemtype;

        return $this;
    }

    public function getItemsId(): ?int
    {
        return $this->items_id;
    }

    public function setItemsId(int $items_id): self
    {
        $this->items_id = $items_id;

        return $this;
    }

    public function getSolutiontypesId(): ?int
    {
        return $this->solutiontypes_id;
    }

    public function setSolutiontypesId(int $solutiontypes_id): self
    {
        $this->solutiontypes_id = $solutiontypes_id;

        return $this;
    }

    public function getSolutiontypeName(): ?string
    {
        return $this->solutiontype_name;
    }

    public function setSolutiontypeName(?string $solutiontype_name): self
    {
        $this->solutiontype_name = $solutiontype_name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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

    public function getDateApproval(): ?\DateTimeInterface
    {
        return $this->date_approval;
    }

    public function setDateApproval(\DateTimeInterface $date_approval): self
    {
        $this->date_approval = $date_approval;

        return $this;
    }

    public function getUsersId(): ?int
    {
        return $this->users_id;
    }

    public function setUsersId(int $users_id): self
    {
        $this->users_id = $users_id;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function setUserName(string $user_name): self
    {
        $this->user_name = $user_name;

        return $this;
    }

    public function getUsersIdEditor(): ?int
    {
        return $this->users_id_editor;
    }

    public function setUsersIdEditor(int $user_id_editor): self
    {
        $this->users_id_editor = $user_id_editor;

        return $this;
    }

    public function getUserIdApproval(): ?int
    {
        return $this->users_id_approval;
    }

    public function setUsersIdApproval(int $user_id_approval): self
    {
        $this->users_id_approval = $user_id_approval;

        return $this;
    }

    public function getUsersNameApproval(): ?string
    {
        return $this->user_name_approval;
    }

    public function setUserNameApproval(string $user_name_approval): self
    {
        $this->user_name_approval = $user_name_approval;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getItilFollowupsId(): ?int
    {
        return $this->itilfollowups_id;
    }

    public function setItilFollowupsId(int $itil_followups_id): self
    {
        $this->itilfollowups_id = $itil_followups_id;

        return $this;
    }
}
