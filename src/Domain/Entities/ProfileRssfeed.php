<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_profiles_rssfeeds')]
#[ORM\Index(name: "rssfeeds_id", columns: ["rssfeeds_id"])]
#[ORM\Index(name: "profiles_id", columns: ["profiles_id"])]
#[ORM\Index(name: "entities_id", columns: ["entities_id"])]
#[ORM\Index(name: "is_recursive", columns: ["is_recursive"])]
class ProfileRssfeed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Rssfeed::class, inversedBy: 'profileRssfeeds')]
    #[ORM\JoinColumn(name: 'rssfeeds_id', referencedColumnName: 'id', nullable: true)]
    private ?Rssfeed $rssfeed;

    #[ORM\ManyToOne(targetEntity: Profile::class, inversedBy: 'profileRssfeeds')]
    #[ORM\JoinColumn(name: 'profiles_id', referencedColumnName: 'id', nullable: true)]
    private ?Profile $profile;

    #[ORM\Column(type: 'integer', options: ['default' => -1])]
    private $entities_id;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $is_recursive;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntitiesId(): ?int
    {
        return $this->entities_id;
    }


    public function setEntitiesId(?int $entities_id): self
    {
        $this->entities_id = $entities_id;

        return $this;
    }

    public function getIsRecursive(): ?bool
    {
        return $this->is_recursive;
    }


    public function setIsRecursive(?bool $is_recursive): self
    {
        $this->is_recursive = $is_recursive;

        return $this;
    }


    /**
     * Get the value of rssfeed
     */ 
    public function getRssfeed()
    {
        return $this->rssfeed;
    }

    /**
     * Set the value of rssfeed
     *
     * @return  self
     */ 
    public function setRssfeed($rssfeed)
    {
        $this->rssfeed = $rssfeed;

        return $this;
    }

    /**
     * Get the value of profile
     */ 
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set the value of profile
     *
     * @return  self
     */ 
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }
}
