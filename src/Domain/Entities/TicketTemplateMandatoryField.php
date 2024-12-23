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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: TicketTemplate::class)]
    #[ORM\JoinColumn(name: 'tickettemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?TicketTemplate $tickettemplate;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $num;

    public function getId(): ?int
    {
        return $this->id;
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


    /**
     * Get the value of tickettemplate
     */ 
    public function getTickettemplate()
    {
        return $this->tickettemplate;
    }

    /**
     * Set the value of tickettemplate
     *
     * @return  self
     */ 
    public function setTickettemplate($tickettemplate)
    {
        $this->tickettemplate = $tickettemplate;

        return $this;
    }
}
