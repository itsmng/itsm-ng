<?php

namespace Itsmng\Domain\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'glpi_changetemplatemandatoryfields')]
#[ORM\UniqueConstraint(name: 'unicity', columns: ['changetemplates_id', 'num'])]
#[ORM\Index(name: 'changetemplates_id', columns: ['changetemplates_id'])]

class ChangeTemplateMandatoryField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: ChangeTemplate::class)]
    #[ORM\JoinColumn(name: 'changetemplates_id', referencedColumnName: 'id', nullable: true)]
    private ?ChangeTemplate $changetemplate = null;

    #[ORM\Column(name: 'num', type: 'integer', options: ['default' => 0])]
    private $num = 0;

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * Get the value of changetemplate
     */
    public function getChangetemplate()
    {
        return $this->changetemplate;
    }

    /**
     * Set the value of changetemplate
     *
     * @return  self
     */
    public function setChangetemplate($changetemplate)
    {
        $this->changetemplate = $changetemplate;

        return $this;
    }
}
