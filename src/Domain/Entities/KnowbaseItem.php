<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
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
class KnowbaseItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: KnowbaseItemCategory::class)]
    #[ORM\JoinColumn(name: 'knowbaseitemcategories_id', referencedColumnName: 'id', nullable: true)]
    private ?KnowbaseItemCategory $knowbaseitemcategory = null;

    #[ORM\Column(name: 'name', type: "text", nullable: true, length: 65535)]
    private $name;

    #[ORM\Column(name: 'answer', type: "text", nullable: true)]
    private $answer;

    #[ORM\Column(name: 'is_faq', type: "boolean", options: ["default" => 0])]
    private $isFaq = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'view', type: "integer", options: ["default" => 0])]
    private $view = 0;

    #[ORM\Column(name: 'date', type: "datetime", nullable: true)]
    private $date;

    #[ORM\Column(name: 'date_mod', type: "datetime")]
    private $dateMod;

    #[ORM\Column(name: 'begin_date', type: "datetime", nullable: true)]
    private $beginDate;

    #[ORM\Column(name: 'end_date', type: "datetime", nullable: true)]
    private $endDate;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: EntityKnowbaseItem::class)]
    private Collection $entityKnowbaseItems;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: GroupKnowbaseItem::class)]
    private Collection $groupKnowbaseItems;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: KnowbaseItemProfile::class)]
    private Collection $knowbaseitemProfiles;

    #[ORM\OneToMany(mappedBy: 'knowbaseitem', targetEntity: KnowbaseItemUser::class)]
    private Collection $knowbaseitemUsers;

    public function __construct()
    {
        $this->entityKnowbaseItems = new ArrayCollection();
        $this->groupKnowbaseItems = new ArrayCollection();
        $this->knowbaseitemProfiles = new ArrayCollection();
        $this->knowbaseitemUsers = new ArrayCollection();
    }

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

    public function setDate(\DateTimeInterface|string|null $date): self
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        $this->date = $date;
        return $this;
    }

    public function getDateMod(): DateTime
    {
        return $this->dateMod ?? new DateTime();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setDateMod(): self
    {
        $this->dateMod = new DateTime();

        return $this;
    }

    public function getBeginDate(): ?\DateTimeInterface
    {
        return $this->beginDate;
    }

    public function setBeginDate(\DateTimeInterface|string|null $beginDate): self
    {
        if (is_string($beginDate)) {
            $beginDate = new \DateTime($beginDate);
        }
        $this->beginDate = $beginDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface|string|null $endDate): self
    {
        if (is_string($endDate)) {
            $endDate = new \DateTime($endDate);
        }
        $this->endDate = $endDate;
        return $this;
    }


    /**
     * Get the value of entityKnowbaseItems
     */
    public function getEntityKnowbaseItems()
    {
        return $this->entityKnowbaseItems;
    }

    /**
     * Set the value of entityKnowbaseItems
     *
     * @return  self
     */
    public function setEntityKnowbaseItems($entityKnowbaseItems)
    {
        $this->entityKnowbaseItems = $entityKnowbaseItems;

        return $this;
    }

    /**
     * Get the value of groupKnowbaseItems
     */
    public function getGroupKnowbaseItems()
    {
        return $this->groupKnowbaseItems;
    }

    /**
     * Set the value of groupKnowbaseItems
     *
     * @return  self
     */
    public function setGroupKnowbaseItems($groupKnowbaseItems)
    {
        $this->groupKnowbaseItems = $groupKnowbaseItems;

        return $this;
    }

    /**
     * Get the value of knowbaseitemcategory
     */
    public function getKnowbaseItemCategory()
    {
        return $this->knowbaseitemcategory;
    }

    /**
     * Set the value of knowbaseitemcategory
     *
     * @return  self
     */
    public function setKnowbaseItemCategory($knowbaseitemcategory)
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
    public function getKnowbaseItemProfiles()
    {
        return $this->knowbaseitemProfiles;
    }

    /**
     * Set the value of knowbaseitemProfiles
     *
     * @return  self
     */
    public function setKnowbaseItemProfiles($knowbaseitemProfiles)
    {
        $this->knowbaseitemProfiles = $knowbaseitemProfiles;

        return $this;
    }

    /**
     * Get the value of knowbaseitemUsers
     */
    public function getKnowbaseItemUsers()
    {
        return $this->knowbaseitemUsers;
    }

    /**
     * Set the value of knowbaseitemUsers
     *
     * @return  self
     */
    public function setKnowbaseItemUsers($knowbaseitemUsers)
    {
        $this->knowbaseitemUsers = $knowbaseitemUsers;

        return $this;
    }
}
