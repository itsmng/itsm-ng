<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changetemplatepredefinedfields')]
#[ORM\Index(name: 'changetemplates_id', columns: ['changetemplates_id'])]

class Changetemplatepredefinedfield
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changetemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $num;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChangetemplatesId(): ?int
    {
        return $this->changetemplates_id;
    }

    public function setChangetemplatesId(int $changetemplates_id): self
    {
        $this->changetemplates_id = $changetemplates_id;

        return $this;
    }

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function setNum(int $num): self
    {
        $this->num = $num;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
