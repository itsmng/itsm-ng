<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_profilerights')]
#[ORM\UniqueConstraint(name: "unicity", columns: ["profiles_id", "name"])]
class ProfileRight
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Profile::class)]
    #[ORM\JoinColumn(name: 'profiles_id', referencedColumnName: 'id', nullable: true)]
    private ?Profile $profile = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(name: 'rights', type: 'integer', options: ['default' => 0])]
    private $rights = 0;

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

    public function getRights(): ?int
    {
        return $this->rights;
    }

    /**
     * @param int|string|null $rights Les droits à définir
     * @return self
     */
    public function setRights($rights): self
    {
        // Convertir en entier si nécessaire
        if ($rights !== null) {
            $this->rights = (int)$rights;
        } else {
            $this->rights = null;
        }

        return $this;
    }


    /**
     * @return Profile|null L'objet Profile associé
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * @param Profile|null $profile L'objet Profile à associer
     * @return self
     */
    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

}
