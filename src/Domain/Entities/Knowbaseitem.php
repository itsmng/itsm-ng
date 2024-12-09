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
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Knowbaseitemcategory::class)]
    #[ORM\JoinColumn(name: 'knowbaseitemcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?Knowbaseitemcategory $knowbaseitemcategory;

    #[ORM\Column(type: "text", nullable: true, length: 65535)]
    private $name;

    #[ORM\Column(type: "text", nullable: true)]
    private $answer;

    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    private $is_faq;
    
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private $view;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $date_mod;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $begin_date;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $end_date;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: EntityKnowbaseitem::class)]
    private Collection $entityKnowbaseitems;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: GroupKnowbaseItem::class)]
    private Collection $groupKnowbaseitems;

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
        return $this->is_faq;
    }

    public function setIsFaq(?bool $is_faq): self
    {
        $this->is_faq = $is_faq;
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
        return $this->date_mod;
    }

    public function setDateMod(\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;
        return $this;
    }

    public function getBeginDate(): ?\DateTimeInterface
    {
        return $this->begin_date;
    }

    public function setBeginDate(\DateTimeInterface $begin_date): self
    {
        $this->begin_date = $begin_date;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): self
    {
        $this->end_date = $end_date;
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
}
