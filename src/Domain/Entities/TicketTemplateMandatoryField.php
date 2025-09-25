<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "glpi_tickettemplatemandatoryfields")]
#[ORM\UniqueConstraint(name: "unicity", columns: ["tickettemplates_id", "num"])]
#[ORM\Index(name: "tickettemplates_id", columns: ["tickettemplates_id"])]
class TicketTemplateMandatoryField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\Column(name: 'tickettemplates_id', type: 'integer', options: ['default' => 0])]
    private $tickettemplatesId = 0;

    #[ORM\Column(name: 'num', type: 'integer', options: ['default' => 0])]
    private $num = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTickettemplatesId(): ?int
    {
        return $this->tickettemplatesId;
    }

    public function setTickettemplatesId(?int $tickettemplatesId): self
    {
        $this->tickettemplatesId = $tickettemplatesId;

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
