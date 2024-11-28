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
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $olalevels_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $action_type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $field;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOlalevelsId(): ?int
    {
        return $this->olalevels_id;
    }

    public function setOlalevelsId(?int $olalevels_id): self
    {
        $this->olalevels_id = $olalevels_id;

        return $this;
    }

    public function getActionType(): ?string
    {
        return $this->action_type;
    }

    public function setActionType(?string $action_type): self
    {
        $this->action_type = $action_type;

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

}
