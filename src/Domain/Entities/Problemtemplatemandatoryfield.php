<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_problemtemplatemandatoryfields')]
#[ORM\UniqueConstraint(name: "unicity", columns: ["problemtemplates_id", "num"])]
#[ORM\Index(name: "problemtemplates_id", columns: ["problemtemplates_id"])]
class Problemtemplatemandatoryfield
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Problemtemplate::class)]
    #[ORM\JoinColumn(name: 'problemtemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?Problemtemplate $problemtemplate = null;

    #[ORM\Column(name: 'num', type: 'integer', options: ['default' => 0])]
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
