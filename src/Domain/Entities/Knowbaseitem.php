<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "glpi_knowbaseitems")]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "knowbaseitemcategories_id", columns: ["knowbaseitemcategories_id"])]
#[ORM\Index(name: "is_faq", columns: ["is_faq"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "begin_date", columns: ["begin_date"])]
#[ORM\Index(name: "end_date", columns: ["end_date"])]
#[ORM\Index(name: "fulltext", columns: ["name", "answer"], flags: ["FULLTEXT"])]
#[ORM\Index(name: "name", columns: ["name"], flags: ["FULLTEXT"])]
#[ORM\Index(name: "answer", columns: ["answer"], flags: ["FULLTEXT"])]
class Knowbaseitem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitemcategory::class)]
    #[ORM\JoinColumn(name: 'knowbaseitemcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Knowbaseitemcategory $knowbaseitemcategory = null;

    #[ORM\Column(name: 'name', type: "text", nullable: true, length: 65535)]
    private $name;

    #[ORM\Column(name: 'answer', type: "text", nullable: true)]
    private $answer;

    #[ORM\Column(name: 'is_faq', type: "boolean", options: ["default" => 0])]
    private $isFaq;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'view', type: "integer", options: ["default" => 0])]
    private $view;

    #[ORM\Column(name: 'date', type: "datetime", nullable: true)]
    private $date;

    #[ORM\Column(name: 'date_mod', type: "datetime", nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'begin_date', type: "datetime", nullable: true)]
    private $beginDate;

    #[ORM\Column(name: 'end_date', type: "datetime", nullable: true)]
    private $endDate;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: EntityKnowbaseitem::class)]
    private Collection $entityKnowbaseitems;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: GroupKnowbaseItem::class)]
    private Collection $groupKnowbaseitems;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: KnowbaseitemProfile::class)]
    private Collection $knowbaseitemProfiles;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: KnowbaseitemUser::class)]
    private Collection $knowbaseitemUsers;

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

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): self
    {
        $this->answer = $answer;
        return $this;
    }

    public function getIsFaq(): ?bool
    {
        return $this->isFaq;
    }

    public function setIsFaq(?bool $isFaq): self
    {
        $this->isFaq = $isFaq;
        return $this;
    }

    public function getView(): ?int
    {
        return $this->view;
    }

    public function setView(int $view): self
    {
        $this->view = $view;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
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

    public function getBeginDate(): ?\DateTimeInterface
    {
        return $this->beginDate;
    }

    public function setBeginDate(\DateTimeInterface $beginDate): self
    {
        $this->beginDate = $beginDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }


    /**
     * Get the value of entityKnowbaseitems
     */
    public function getEntityKnowbaseitems()
    {
        return $this->entityKnowbaseitems;
    }

    /**
     * Set the value of entityKnowbaseitems
     *
     * @return  self
     */
    public function setEntityKnowbaseitems($entityKnowbaseitems)
    {
        $this->entityKnowbaseitems = $entityKnowbaseitems;

        return $this;
    }

    /**
     * Get the value of groupKnowbaseitems
     */
    public function getGroupKnowbaseitems()
    {
        return $this->groupKnowbaseitems;
    }

    /**
     * Set the value of groupKnowbaseitems
     *
     * @return  self
     */
    public function setGroupKnowbaseitems($groupKnowbaseitems)
    {
        $this->groupKnowbaseitems = $groupKnowbaseitems;

        return $this;
    }

    /**
     * Get the value of knowbaseitemcategory
     */
    public function getKnowbaseitemcategory()
    {
        return $this->knowbaseitemcategory;
    }

    /**
     * Set the value of knowbaseitemcategory
     *
     * @return  self
     */
    public function setKnowbaseitemcategory($knowbaseitemcategory)
    {
        $this->knowbaseitemcategory = $knowbaseitemcategory;

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
     * Get the value of knowbaseitemProfiles
     */
    public function getKnowbaseitemProfiles()
    {
        return $this->knowbaseitemProfiles;
    }

    /**
     * Set the value of knowbaseitemProfiles
     *
     * @return  self
     */
    public function setKnowbaseitemProfiles($knowbaseitemProfiles)
    {
        $this->knowbaseitemProfiles = $knowbaseitemProfiles;

        return $this;
    }

    /**
     * Get the value of knowbaseitemUsers
     */
    public function getKnowbaseitemUsers()
    {
        return $this->knowbaseitemUsers;
    }

    /**
     * Set the value of knowbaseitemUsers
     *
     * @return  self
     */
    public function setKnowbaseitemUsers($knowbaseitemUsers)
    {
        $this->knowbaseitemUsers = $knowbaseitemUsers;

        return $this;
    }
}
