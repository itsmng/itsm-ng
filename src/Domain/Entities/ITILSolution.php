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
class ITILSolution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'itemtype', type: 'string', length: 100)]
    private $itemtype;

    #[ORM\Column(name: 'items_id', type: 'integer', options: ['default' => 0])]
    private $itemsId;

    #[ORM\ManyToOne(targetEntity: SolutionType::class)]
    #[ORM\JoinColumn(name: 'solutiontypes_id', referencedColumnName: 'id', nullable: true)]
    private ?SolutionType $solutiontype = null;

    #[ORM\Column(name: 'solutiontype_name', type: 'string', length: 255, nullable: true)]
    private $solutiontypeName;

    #[ORM\Column(name: 'content', type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_approval', type: 'datetime', nullable: true)]
    private $dateApproval;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'user_name', type: 'string', length: 255, nullable: true)]
    private $userName;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_editor', referencedColumnName: 'id', nullable: true)]
    private ?User $userEditor = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id_approval', referencedColumnName: 'id', nullable: true)]
    private ?User $userApproval = null;


    #[ORM\Column(name: 'user_name_approval', type: 'string', length: 255, nullable: true)]
    private $userNameApproval;

    #[ORM\Column(name: 'status', type: 'integer', options: ['default' => 1])]
    private $status;

    #[ORM\ManyToOne(targetEntity: ITILFollowup::class)]
    #[ORM\JoinColumn(name: 'itilfollowups_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'Followup reference on reject or approve a solution'])]
    private ?ITILFollowup $itilFollowup = null;


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
        return $this->itemsId;
    }

    public function setItemsId(int $itemsId): self
    {
        $this->itemsId = $itemsId;

        return $this;
    }

    public function getSolutionTypeName(): ?string
    {
        return $this->solutiontypeName;
    }

    public function setSolutionTypeName(?string $solutiontypeName): self
    {
        $this->solutiontypeName = $solutiontypeName;

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
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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

    public function getDateApproval(): ?\DateTimeInterface
    {
        return $this->dateApproval;
    }

    public function setDateApproval(\DateTimeInterface $dateApproval): self
    {
        $this->dateApproval = $dateApproval;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getUsersNameApproval(): ?string
    {
        return $this->userNameApproval;
    }

    public function setUserNameApproval(string $userNameApproval): self
    {
        $this->userNameApproval = $userNameApproval;

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

    /**
     * Get the value of solutiontype
     */
    public function getSolutionType()
    {
        return $this->solutiontype;
    }

    /**
     * Set the value of solutiontype
     *
     * @return  self
     */
    public function setSolutionType($solutiontype)
    {
        $this->solutiontype = $solutiontype;

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
     * Get the value of userEditor
     */
    public function getUserEditor()
    {
        return $this->userEditor;
    }

    /**
     * Set the value of userEditor
     *
     * @return  self
     */
    public function setUserEditor($userEditor)
    {
        $this->userEditor = $userEditor;

        return $this;
    }

    /**
     * Get the value of userApproval
     */
    public function getUserApproval()
    {
        return $this->userApproval;
    }

    /**
     * Set the value of userApproval
     *
     * @return  self
     */
    public function setUserApproval($userApproval)
    {
        $this->userApproval = $userApproval;

        return $this;
    }

    /**
     * Get the value of itilFollowup
     */
    public function getITILFollowup()
    {
        return $this->itilFollowup;
    }

    /**
     * Set the value of itilFollowup
     *
     * @return  self
     */
    public function setITILFollowup($itilFollowup)
    {
        $this->itilFollowup = $itilFollowup;

        return $this;
    }
}
