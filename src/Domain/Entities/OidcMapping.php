<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_oidc_mapping')]
class OidcMapping
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $given_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $family_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $picture;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $locale;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $phone_number;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $group;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $date_mod;



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

    public function getGivenName(): ?string
    {
        return $this->given_name;
    }

    public function setGivenName(?string $given_name): self
    {
        $this->given_name = $given_name;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->family_name;
    }

    public function setFamilyName(?string $family_name): self
    {
        $this->family_name = $family_name;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?string $phone_number): self
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getDateMod(): ?\DateTimeInterface
    {
        return $this->date_mod;
    }

    public function setDateMod(?\DateTimeInterface $date_mod): self
    {
        $this->date_mod = $date_mod;

        return $this;
    }

}
