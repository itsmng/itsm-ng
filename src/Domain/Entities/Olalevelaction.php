<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_olalevelactions')]
#[ORM\Index(name: 'olalevels_id', columns: ['olalevels_id'])]
class Olalevelaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Olalevel::class)]
    #[ORM\JoinColumn(name: 'olalevels_id', referencedColumnName: 'id', nullable: true)]
    private ?Olalevel $olalevel = null;


    #[ORM\Column(name: 'action_type', type: 'string', length: 255, nullable: true)]
    private $actionType;

    #[ORM\Column(name: 'field', type: 'string', length: 255, nullable: true)]
    private $field;

    #[ORM\Column(name: 'value', type: 'string', length: 255, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(?string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

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


    /**
     * Get the value of olalevel
     */
    public function getOlalevel()
    {
        return $this->olalevel;
    }

    /**
     * Set the value of olalevel
     *
     * @return  self
     */
    public function setOlalevel($olalevel)
    {
        $this->olalevel = $olalevel;

        return $this;
    }
}
