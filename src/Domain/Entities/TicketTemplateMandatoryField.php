<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickettemplatemandatoryfields")]
#[ORM\UniqueConstraint(name: "tickettemplates_id_num", columns: ["tickettemplates_id", "num"])]
#[ORM\Index(name: "tickettemplates_id", columns: ["tickettemplates_id"])]
class TicketTemplateMandatoryField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $tickettemplates_id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $num;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTickettemplatesId(): ?int
    {
        return $this->tickettemplates_id;
    }

    public function setTickettemplatesId(?int $tickettemplates_id): self
    {
        $this->tickettemplates_id = $tickettemplates_id;

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
