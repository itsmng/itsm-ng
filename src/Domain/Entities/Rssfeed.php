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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $users_id;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $comment;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $url;

    #[ORM\Column(type: 'integer', options: ['default' => 86400])]
    private $refresh_rate;

    #[ORM\Column(type: 'integer', options: ['default' => 20])]
    private $max_items;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $have_error;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_active;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_creation;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: EntityRssFeed::class)]
    private Collection $entityRssfeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: GroupRssFeed::class)]
    private Collection $groupRssfeeds;

    #[ORM\OneToMany(mappedBy: 'rssfeed', targetEntity: ProfileRssfeed::class)]
    private Collection $profileRssfeeds;

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

    public function getUsersId(): ?string
    {
        return $this->users_id;
    }

    public function setUsersId(?string $users_id): self
    {
        $this->users_id = $users_id;

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
        return $this->refresh_rate;
    }

    public function setRefreshRate(?string $refresh_rate): self
    {
        $this->refresh_rate = $refresh_rate;

        return $this;
    }

    public function getMaxItems(): ?string
    {
        return $this->max_items;
    }

    public function setMaxItems(?string $max_items): self
    {
        $this->max_items = $max_items;

        return $this;
    }

    public function getHaveError(): ?string
    {
        return $this->have_error;
    }

    public function setHaveError(?string $have_error): self
    {
        $this->have_error = $have_error;

        return $this;
    }

    public function getIsActive(): ?string
    {
        return $this->is_active;
    }

    public function setIsActive(?string $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getDateMod(): ?string
    {
        return $this->date_mod;
    }

    public function setDateMod(?string $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

    public function getDateCreation(): ?string
    {
        return $this->date_creation;
    }

    public function setDateCreation(?string $date_creation): self
    {
        $this->date_creation = $date_creation;

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
}
