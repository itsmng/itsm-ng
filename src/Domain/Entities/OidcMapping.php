<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_oidc_mapping')]
class OidcMapping
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', options: ['default' => 0])]
    private $id;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $name;

    #[ORM\Column(name: 'given_name', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $givenName;

    #[ORM\Column(name: 'family_name', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $familyName;

    #[ORM\Column(name: 'picture', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $picture;

    #[ORM\Column(name: 'email', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $email;

    #[ORM\Column(name: 'locale', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $locale;

    #[ORM\Column(name: 'phone_number', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $phoneNumber;

    #[ORM\Column(name: 'group', type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    private $group;

    #[ORM\Column(name: 'date_mod', type: 'datetime', nullable: true)]
    private $dateMod;



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
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

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
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

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
        return $this->dateMod;
    }

    public function setDateMod(?\DateTimeInterface $dateMod): self
    {
        $this->dateMod = $dateMod;

        return $this;
    }

}
