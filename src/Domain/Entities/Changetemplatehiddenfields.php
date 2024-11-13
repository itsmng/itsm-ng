<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changetemplatehiddenfields')]
#[ORM\UniqueConstraint(name: 'changetemplates_id_num', columns: ['changetemplates_id', 'num'])]
#[ORM\Index(name: 'changetemplates_id', columns: ['changetemplates_id'])]

class Changetemplatehiddenfields
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $changetemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $num;

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
}