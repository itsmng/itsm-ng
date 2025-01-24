<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_rssfeeds')]
#[ORM\Index(name: "name", columns: ["name"])]
#[ORM\Index(name: "users_id", columns: ["users_id"])]
#[ORM\Index(name: "date_mod", columns: ["date_mod"])]
#[ORM\Index(name: "have_error", columns: ["have_error"])]
#[ORM\Index(name: "is_active", columns: ["is_active"])]
#[ORM\Index(name: "date_creation", columns: ["date_creation"])]
class Rssfeed
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
    private $refreshRate;

    #[ORM\Column(name: 'max_items', type: 'integer', options: ['default' => 20])]
    private $maxItems;

    #[ORM\Column(name: 'have_error', type: 'boolean', options: ['default' => 0])]
    private $haveError;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => 0])]
    private $isActive;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;

    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: true)]
    private $dateCreation;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: EntityRssFeed::class)]
    private Collection $entityRssfeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: GroupRssFeed::class)]
    private Collection $groupRssfeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: ProfileRssfeed::class)]
    private Collection $profileRssfeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: RssfeedUser::class)]
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

    public function getRefreshRate(): ?string
    {
        return $this->refreshRate;
    }

    public function setRefreshRate(?string $refreshRate): self
    {
        $this->refreshRate = $refreshRate;

        return $this;
    }

    public function getMaxItems(): ?string
    {
        return $this->maxItems;
    }

    public function setMaxItems(?string $maxItems): self
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

    public function getDateMod(): ?string
    {
        return $this->dateMod;
    }

    public function setDateMod(?string $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?string $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }


    /**
     * Get the value of entityRssfeeds
     */
    public function getEntityRssfeeds()
    {
        return $this->entityRssfeeds;
    }

    /**
     * Set the value of entityRssfeeds
     *
     * @return  self
     */
    public function setEntityRssfeeds($entityRssfeeds)
    {
        $this->entityRssfeeds = $entityRssfeeds;

        return $this;
    }

    /**
     * Get the value of groupRssfeeds
     */
    public function getGroupRssfeeds()
    {
        return $this->groupRssfeeds;
    }

    /**
     * Set the value of groupRssfeeds
     *
     * @return  self
     */
    public function setGroupRssfeeds($groupRssfeeds)
    {
        $this->groupRssfeeds = $groupRssfeeds;

        return $this;
    }

    /**
     * Get the value of profileRssfeeds
     */
    public function getProfileRssfeeds()
    {
        return $this->profileRssfeeds;
    }

    /**
     * Set the value of profileRssfeeds
     *
     * @return  self
     */
    public function setProfileRssfeeds($profileRssfeeds)
    {
        $this->profileRssfeeds = $profileRssfeeds;

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
    public function getRssfeedUsers()
    {
        return $this->rssfeedUsers;
    }

    /**
     * Set the value of rssfeedUsers
     *
     * @return  self
     */
    public function setRssfeedUsers($rssfeedUsers)
    {
        $this->rssfeedUsers = $rssfeedUsers;

        return $this;
    }
}
