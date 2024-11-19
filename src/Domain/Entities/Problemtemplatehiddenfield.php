<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problemtemplatehiddenfields')]
#[ORM\UniqueConstraint(columns: ["problemtemplates_id", "num"])]
#[ORM\Index(name: "problemtemplates_id", columns: ["problemtemplates_id"])]
class Problemtemplatehiddenfield
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $problemtemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $num;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProblemtemplatesId(): ?int
    {
        return $this->problemtemplates_id;
    }


    public function setProblemtemplatesId(?int $problemtemplates_id): self
    {
        $this->problemtemplates_id = $problemtemplates_id;

        return $this;
    }

    public function getNum(): ?int
    {
        return $this->num;
    }


    public function setNum(?int $num): self
    {
        $this->num = $num;

        return $this;
    }

}
