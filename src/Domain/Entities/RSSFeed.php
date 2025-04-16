<?php

namespace Itsmng\Domain\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'glpi_rssfeeds')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "have_error", columns: ["have_error"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class RSSFeed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'users_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(name: 'url', type: 'text', length: 65535, nullable: true)]
    private $url;

    #[ORM\Column(name: 'refresh_rate', type: 'integer', options: ['default' => 86400])]
    private $refreshRate = 86400;

    #[ORM\Column(name: 'max_items', type: 'integer', options: ['default' => 20])]
    private $maxItems = 20;

    #[ORM\Column(name: 'have_error', type: 'boolean', options: ['default' => 0])]
    private $haveError = 0;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 0])]
    private $isActive = 0;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: EntityRSSFeed::class)]
    private Collection $entityRSSFeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: GroupRSSFeed::class)]
    private Collection $groupRSSFeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: ProfileRSSFeed::class)]
    private Collection $profileRSSFeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: RSSFeedUser::class)]
    private Collection $rssfeedUsers;

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getRefreshRate(): ?int
    {
        return $this->refreshRate;
    }

    public function setRefreshRate(?int $refreshRate): self
    {
        $this->refreshRate = $refreshRate;

        return $this;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    public function setMaxItems(?int $maxItems): self
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    public function getHaveError(): ?string
    {
        return $this->haveError;
    }

    public function setHaveError(?string $haveError): self
    {
        $this->haveError = $haveError;

        return $this;
    }

    public function getIsActive(): ?string
    {
        return $this->isActive;
    }

    public function setIsActive(?string $isActive): self
    {
        $this->isActive = $isActive;

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
    
    public function getDateCreation(): DateTime
    {
        return $this->dateCreation ?? new DateTime();
    }

    #[ORM\PrePersist]
    public function setDateCreation(): self
    {
        $this->dateCreation = new DateTime();

        return $this;
    }


    /**
     * Get the value of entityRSSFeeds
     */
    public function getEntityRSSFeeds(): Collection
    {
        if (!isset($this->entityRSSFeeds)) {
            $this->entityRSSFeeds = new ArrayCollection();
        }
        return $this->entityRSSFeeds;
    }

    /**
     * Set the value of entityRSSFeeds
     *
     * @return  self
     */
    public function setEntityRSSFeeds(?Collection $entityRSSFeeds): self
    {
        $this->entityRSSFeeds = $entityRSSFeeds ?? new ArrayCollection();

        return $this;
    }

    /**
     * Get the value of groupRSSFeeds
     */
    public function getGroupRSSFeeds(): Collection
    {
        if (!isset($this->groupRSSFeeds)) {
            $this->groupRSSFeeds = new ArrayCollection();
        }
        return $this->groupRSSFeeds;
    }

    /**
     * Set the value of groupRSSFeeds
     *
     * @return  self
     */
    public function setGroupRSSFeeds(?Collection $groupRSSFeeds): self
    {
        $this->groupRSSFeeds = $groupRSSFeeds ?? new ArrayCollection();

        return $this;
    }

    /**
     * Get the value of profileRSSFeeds
     */
    public function getProfileRSSFeeds(): Collection
    {
        if (!isset($this->profileRSSFeeds)) {
            $this->profileRSSFeeds = new ArrayCollection();
        }
        return $this->profileRSSFeeds;
    }

    /**
     * Set the value of profileRSSFeeds
     *
     * @return  self
     */
    public function setProfileRSSFeeds(?Collection $profileRSSFeeds)
    {
        $this->profileRSSFeeds = $profileRSSFeeds ?? new ArrayCollection();

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
     * Get the value of rssfeedUsers
     */
    public function getRSSFeedUsers(): Collection
    {
        if (!isset($this->rssfeedUsers)) {
            $this->rssfeedUsers = new ArrayCollection();
        }
        return $this->rssfeedUsers;
    }

    /**
     * Set the value of rssfeedUsers
     *
     * @return  self
     */
    public function setRSSFeedUsers(?Collection $rssfeedUsers): self
    {
        $this->rssfeedUsers = $rssfeedUsers ?? new ArrayCollection();

        return $this;
    }
}
