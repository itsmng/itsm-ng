<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problemtemplatepredefinedfields')]
#[ORM\Index(name: "problemtemplates_id", columns: ["problemtemplates_id"])]
class Problemtemplatepredefinedfield
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Problemtemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Problemtemplate $problemtemplate;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private $num;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private $value;

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
     * Get the value of problemtemplate
     */
    public function getProblemtemplate()
    {
        return $this->problemtemplate;
    }

    /**
     * Set the value of problemtemplate
     *
     * @return  self
     */
    public function setProblemtemplate($problemtemplate)
    {
        $this->problemtemplate = $problemtemplate;

        return $this;
    }
}
