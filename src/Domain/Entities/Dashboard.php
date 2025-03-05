<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_dashboards')]
#[ORM\UniqueConstraint(name: 'profileId_userId', columns: ['profileId', 'userId'])]

class Dashboard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 100)]
    private $name;

    #[ORM\Column(name: 'content', type: 'text')]
    private $content;

    #[ORM\ManyToOne(targetEntity: Profile::class)]
    #[ORM\JoinColumn(name: 'profileId', referencedColumnName: 'id', nullable: true)]
    private ?Profile $profile = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'userId', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
